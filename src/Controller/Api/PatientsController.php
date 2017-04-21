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
        'apiEndPointHost' => $host,
        'liveApiEndPointHost' => Configure::read('application.livePhUrl')
      ]);
        $this->loadComponent('RequestHandler');

    }


    public function registerPatient(){
       $this->request->data['name'] = $this->request->data['first_name'].' '.$this->request->data['last_name'];
       $response = $this->Peoplehub->requestData('post', 'user', 'register', false, false, $this->request->data);
       $response->data->vendor_id = $this->request->data['vendor_id'];
       // pr($response); die;
       $this->_fireEvent('registerPatient', $response); 
       $this->set('response', $response);
       $this->set('_serialize', 'response');
    }

    public function loginPatient($username = null, $password = null){
        if(isset($this->request->data['username']) && isset($this->request->data['password'])){
            $headerData = ['username'=> $this->request->data['username'], 'password'=>$this->request->data['password']];
        }else{     
            $headerData = ['username'=> $username, 'password'=>$password];
        }
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
        $response = [$this->request->data,$response];
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
       if(isset($response->data->guardian_email) && isset($this->request->data['password'])){
         $username = $response->data->guardian_email;
         $password = $this->request->data['password'];
         $this->loginPatient($username, $password);
        }else if(isset($response->data->username) && isset($this->request->data['password'])){
          $username = $response->data->username;
          $password = $this->request->data['password'];
          $this->loginPatient($username, $password);
        }
       $this->set('response', $response);
       $this->set('_serialize', 'response');
    }

    public function getPatientInfo($id){
        $payload = ['vendor_id' => $id];
        $response = $this->Peoplehub->requestData('get', 'user', 'me', false, false, $payload);
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
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

   public function validateSocialUser(){
          return $this->redirect('http://localhost/peoplehub/api/user/social-validate-user?provider=Facebook&vendor_id=2');
      // die;
        // $payload = $this->request->query;
        // $response = $this->Peoplehub->requestData('get', 'user', 'social-validate-user', false, false, $payload);
        // pr($response);die;
        // $this->set('response', $response);
        // $this->set('_serialize', 'response');
    }

    public function socialLogin(){
        $headerData = ['BasicToken'=>$this->request->header('Authorization')];
        $response = $this->Peoplehub->requestData('post', 'user', 'social-login', false, $headerData, false);
        //pr($response);
        $response = json_decode($response);
        $this->set('response', $response->data);
        $this->set('_serialize', 'response');
    }

}

//(folowing api's working fine: registerPatient, loginPatient, forgotPassword)

