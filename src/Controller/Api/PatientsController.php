<?php
namespace Integrateideas\Peoplehub\Controller\Api;

use Integrateideas\Peoplehub\Controller\Api\ApiController;
use Integrateideas\Peoplehub\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception;

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
        $this->loadComponent('Integrateideas/Peoplehub.Peoplehub', [ 
        'clientId' => Configure::read('Peoplehub.clientId'),
        'clientSecret' =>Configure::read('Peoplehub.clientSecret'),
        'userType' => Configure::read('Peoplehub.userType')
      ]);
        $this->loadComponent('RequestHandler');

    }

    public function registerPatient(){
       $data = ['name'=> 'damon', 'email'=> 'damon@test.com', 'password'=>'123456789', 'phone'=>'1234567890'];
       $response = $this->Peoplehub->requestData('post', 'user', 'register', false, false, $data); 
       if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function loginPatient(){

       $headerData = ['username'=> $this->request->data['username'], 'password'=>$this->request->data['username']];
       $response = $this->Peoplehub->requestData('post', 'user', 'login', false, $headerData);
       if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function getPatientActivities(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $response = $this->Peoplehub->requestData('get', 'user', 'activities',  false, $headerData);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function addPatientCard(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $data = ['card_number' => 4444333322221111];
        $response = $this->Peoplehub->requestData('post', 'user', 'user-cards', false, $headerData, $data);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function getPatientCardInfo(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $response = $this->Peoplehub->requestData('get', 'user', 'user-cards', false, $headerData);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function getPatientSpecificCardInfo(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $subResourceId = 1;
        $response = $this->Peoplehub->requestData('get', 'user', 'user-cards', $subResourceId, $headerData);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function forgotPassword(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>123456789];
        $data = ['username' => 'vikie@test.com', 'ref' => 'dfghjkkdfghj'];
        $response = $this->Peoplehub->requestData('post', 'user', 'forgot_password', false, $headerData, $data);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
            $this->_fireEvent('forgotPassword',$response);
       } 
    }

    protected function _fireEvent($name, $data){
        $name = 'PeoplehubPatientApi.'.$name;
        $event = new Event($name, $this, [
                $name => $data
            ]);
        $this->eventManager()->dispatch($event);
        
    }

    public function resetPassword(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $data = ['reset-token' => '$2y$10$PpeXFXmAaG9y1Bw6VvIhqe1DRGeJCEke9XAP8c3YO7Bw6.iesQgH2', 'new_password' => '12345678', 'cnf_password' => '12345678'];
        $response = $this->Peoplehub->requestData('post', 'user', 'reset_password', false, $headerData, $data);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function redeemedCredits(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $data = ['ref' => 'sdfghjk', 'service' => 'amazon/tango'];
        $response = $this->Peoplehub->requestData('post', 'user', 'redeemedCredits', false, $headerData, $data);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       } 
    }

    public function switchAccount(){
        $data = ['account_id' => '1234'];
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $response = $this->Peoplehub->requestData('put', 'user', 'switch_account', false, $headerData, $data);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function editPatient(){
        $data = ['name' => 'damon12'];
        $subResourceId = 6;
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $response = $this->Peoplehub->requestData('put', 'user', 'users', $subResourceId, $headerData, $data);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function getPatientInfo(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $response = $this->Peoplehub->requestData('get', 'user', 'me', false, $headerData);
        if($response['status'] != true){
           throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

    public function logout(){
        $headerData = ['username'=> 'damon@test.com', 'password'=>'123456789'];
        $response = $this->Peoplehub->requestData('post', 'user', 'logout', false, $headerData);
        if($response['status'] != true){
            throw new Exception('Something is wrong');
       }else{
         $this->set('response', $response);
       }
    }

}