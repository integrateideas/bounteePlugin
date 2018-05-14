<?php
namespace Integrateideas\Peoplehub\Controller\Api;

use Integrateideas\Peoplehub\Controller\Api\ApiController;
use Integrateideas\Peoplehub\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\Exception;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\UnauthorizedException;
/**
 * Patients Controller
 *
 * @property \Integrateideas\Peoplehub\Model\Table\PatientsTable $Patients
 */
class AuthorizeNetController extends ApiController
{
    Private $_patient = false;
    Private $_vendorPeoplehubId = false;
    Private $_vendorId = false;
   
    public function initialize()
    {   
        parent::initialize();
        $this->_getVendorEndpoints($this->request->header('mode'));        
        $this->loadComponent('Integrateideas/Peoplehub.Peoplehub', [
            'clientId' => Configure::read('reseller.client_id'),
            'clientSecret' =>Configure::read('reseller.client_secret'),
            'apiEndPointHost' => $this->_host,
            'liveApiEndPointHost' => Configure::read('application.livePhUrl')
        ]);

        $data = $this->request->input('json_decode');

        if(!isset($data->vendor_peoplehub_id)){
            throw new BadRequestException('Vendor peoplehub id is required.');
        }

        if(!isset($data->vendor_id)){
            throw new BadRequestException('Vendor id is required.');
        }

        $this->_vendorPeoplehubId = $data->vendor_peoplehub_id;
        $this->_vendorId = $data->vendor_id;
        $this->_authorizePatient();
        $this->_eventData = [
            'vendor_id' => $this->_vendorId,
            'patient' => $this->_patient 
        ];
    }

    private function _getVendorEndpoints($mode){
        
        if($mode){
            $this->_host = $host = Configure::read('application.livePhUrl');
        }else{
            $this->_host = $host = Configure::read('application.phUrl');
        }
    }


    protected function _fireEvent($name, $data){
        $name = 'AuthorizeNet.'.$name;
        $event = new Event($name, $this, [
                $name => $data
            ]);
        $this->eventManager()->dispatch($event);
        if(isset($event->result)){
            return $event->result;
        }else{
            return false;
        }
        
    }

    private function _authorizePatient(){
        
        $vendorPeoplehubId = $this->_vendorPeoplehubId;
        $vendorId = $this->_vendorId;

        $payload = ['vendor_id' => $vendorPeoplehubId];
        $response = $this->Peoplehub->requestData('get', 'user', 'me', false, false, $payload);
        
        if(!$response || !isset($response->data)){
            throw new UnauthorizedException('You are not logged in.');
        }

        $this->_patient = $response->data;
    }

    public function createProfile(){

        if(!isset($this->request->data['account_data']) || empty($this->request->data['account_data'])){
            throw new BadRequestException('Account details are required.');
        }

        $this->_eventData['account_data'] = $this->request->data['account_data'];
        $response = $this->_fireEvent('createProfile', $this->_eventData);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function getProfile(){

        $response = $this->_fireEvent('getProfile', $this->_eventData);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function editProfile(){

        $response = $this->_fireEvent('editProfile', $this->_eventData);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function deleteProfile(){

        $response = $this->_fireEvent('deleteProfile', $this->_eventData);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function getPaymentRequests(){

        $response = $this->_fireEvent('getPaymentRequests', $this->_eventData);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function getTransactionHistory(){

        $response = $this->_fireEvent('getTransactionHistory', $this->_eventData);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function authorizePayment(){

        if(!isset($this->request->data['payment_request_id']) || empty($this->request->data['payment_request_id'])){
            throw new BadRequestException('Payment request id is required.');
        }

        if(!isset($this->request->data['reason']) || empty($this->request->data['reason'])){
            throw new BadRequestException('Reason is required.');
        }
        $this->_eventData['payment_request_id'] = $this->request->data['payment_request_id'];
        $this->_eventData['reason'] = $this->request->data['reason'];

        $response = $this->_fireEvent('authorizePayment', $this->_eventData);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }
}