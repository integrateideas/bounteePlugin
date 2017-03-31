<?php
namespace Integrateideas\Peoplehub\Controller\Api;

use Integrateideas\Peoplehub\Controller\Api\ApiController;
use Integrateideas\Peoplehub\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Core\Exception\Exception;
use Cake\Core\Exception\BadRequestException;
/**
 * Patients Controller
 *
 * @property \Integrateideas\Peoplehub\Model\Table\PatientsTable $Patients
 */
class PatientsController extends ApiController
{
   
    public function initialize()
    {
        parent::initialize();

        if($this->request->header('mode')){
            $host = Configure::read('application.livePhUrl');
        }else{
            $host = Configure::read('application.phUrl');
        }
        $this->loadComponent('Integrateideas/Peoplehub.Peoplehub', [
        'clientId' => Configure::read('reseller.client_id'),
        'clientSecret' =>Configure::read('reseller.client_secret'),
        'apiEndPointHost' => $host
      ]);
        $this->loadComponent('RequestHandler');

    }


    public function registerPatient(){
       $this->request->data['name'] = $this->request->data['first_name'].' '.$this->request->data['last_name'];
       $response = $this->Peoplehub->requestData('post', 'user', 'register', false, false, $this->request->data);
       $this->_fireEvent('registerPatient', $response); 
       $this->set('response', $response);
       $this->set('_serialize', 'response');
    }

    public function loginPatient(){
       $headerData = ['username'=> $this->request->data['username'], 'password'=>$this->request->data['password']];
       $response = $this->Peoplehub->requestData('post', 'user', 'login', false, $headerData);
       $this->set('response', $response);
       $this->set('_serialize', 'response');
    }

    public function getPatientActivities(){
        $response = $this->Peoplehub->requestData('get', 'user', 'activities',  false, $headerData);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function addPatientCard(){
        $response = $this->Peoplehub->requestData('post', 'user', 'user-cards', false, false, $this->request->data);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function getPatientCardInfo(){
        $response = $this->Peoplehub->requestData('get', 'user', 'user-cards', false);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function getPatientSpecificCardInfo($id){
        $response = $this->Peoplehub->requestData('get', 'user', 'user-cards', $id);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function forgotPassword(){
        $response = $this->Peoplehub->requestData('post', 'user', 'forgot_password', false, false, $this->request->data);
        $this->_fireEvent('forgotPassword',$response);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    protected function _fireEvent($name, $data){
        $name = 'PeoplehubPatientApi.'.$name;
        $event = new Event($name, $this, [
                $name => $data
            ]);
        $this->eventManager()->dispatch($event);
        
    }

    public function resetPassword(){
        $response = $this->Peoplehub->requestData('post', 'user', 'reset_password', false, false, $this->request->data);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function redeemedCredits(){
        $this->_fireEvent('beforeRedemption', $this->request->data);
        $response = $this->Peoplehub->requestData('post', 'user', 'redeemedCredits', false, false, $this->request->data);
        $this->_fireEvent('afterRedemption', $response);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function switchAccount(){
        $response = $this->Peoplehub->requestData('put', 'user', 'switch_account', false, false, $this->request->data);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function editPatient($id){
       $response = $this->Peoplehub->requestData('put', 'user', 'users', $id, false, $this->request->data);
       $this->set('response', $response);
       $this->set('_serialize', 'response');
    }

    public function getPatientInfo($id){
        $payload = ['vendor_id' => $id];
        $response = $this->Peoplehub->requestData('get', 'user', 'me', false, false, $payload);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function fbLogin(){
        $response = $this->Peoplehub->requestData('post', 'user', 'fb-login', false, false, $this->request->data);
        $this->set('response', $response);
        $this->set('_serialize', 'response');  
    }

    public function logout(){
        $response = $this->Peoplehub->requestData('post', 'user', 'logout', false);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function referral(){
        $response = $this->request->data;
        $this->_fireEvent('Referrals', $response);
    }

}

//(folowing api's working fine: registerPatient, loginPatient, forgotPassword)
