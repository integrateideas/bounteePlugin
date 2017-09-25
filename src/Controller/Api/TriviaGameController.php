<?php
namespace Integrateideas\Peoplehub\Controller\Api;

use Integrateideas\Peoplehub\Controller\Api\ApiController;
use Integrateideas\Peoplehub\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\Exception;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\InternalErrorException;
/**
 * Patients Controller
 *
 * @property \Integrateideas\Peoplehub\Model\Table\PatientsTable $Patients
 */
class TriviaGameController extends ApiController
{
   
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
        $this->loadComponent('RequestHandler');

    }

    private function _getVendorEndpoints($mode){
        
        if($mode){
            $this->_host = $host = Configure::read('application.livePhUrl');
        }else{
            $this->_host = $host = Configure::read('application.phUrl');
        }
    }

    public function triviaGameWinner(){
        if($this->request->data['identifier_type'] == 'card_number'){
            $card = $this->request->data['identifier_type'];
            $this->request->data['identifier_type'] = substr($card, 0, strpos($card, "_")); 
        }
        $data = [
                       'attributeType' => $this->request->data['identifier_type'],
                       'value' =>  $this->request->data['identifier_value']
                ];
        $searchUser = $this->Peoplehub->requestData('get', 'vendor', 'user-search', false, false, $data, $this->request->data['vendor_id']);
        // pr(count($searchUser)); die;
        if(count($searchUser) == 1){
            $rewardType = $this->_fireEvent('vendorDetails', $this->request->data);
            $payload = [
                                'attribute' => $this->request->data['identifier_value'],
                                'attribute_type' => $this->request->data['identifier_type'],
                                'points' => 50,
                                'reward_type' => $rewardType
                           ];
            $response = $this->Peoplehub->requestData('post', 'vendor', 'rewardCredits', false, false, $payload, $this->request->data['vendor_id']); 
        }else{
            Log::write('debug', "More than one patient corresponding to vendor id ".$this->request->data['vendor_id']);
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }
   

    protected function _fireEvent($name, $data){
        $name = 'TriviaGameWinner.'.$name;
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
    
}

