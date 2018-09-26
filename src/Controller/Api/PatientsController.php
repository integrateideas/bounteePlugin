<?php
namespace Integrateideas\Peoplehub\Controller\Api;

use Integrateideas\Peoplehub\Controller\Api\ApiController;
use Integrateideas\Peoplehub\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\Network\Exception\Exception;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\InternalErrorException;
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
        if(isset($response->status) && $response->status){
            $response = $this->_fireEvent('afterLogin',$response);
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function deviceToken(){
        if(isset($this->request->data['user_device_token']) && $this->request->data['user_device_token']){
            $data = [
                        'user_device_token' => $this->request->data['user_device_token']
                    ];
            $response = $this->Peoplehub->requestData('post', 'user','set_device_token', false, false, $data);
            $this->set('response', $response);
            $this->set('_serialize', 'response');
        }
    }

    public function getPatientActivities(){
        $response = $this->Peoplehub->requestData('get', 'user', 'activities',  false, $headerData);
        if(!$response){
            $this->logout();
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function addPatientCard(){
        $response = $this->Peoplehub->requestData('post', 'user', 'user-cards', false, false, $this->request->data);
        if(!$response){
            $this->logout();
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function getPatientCardInfo(){
        $response = $this->Peoplehub->requestData('get', 'user', 'user-cards', false);
        if(!$response){
            $this->logout();
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function getPatientSpecificCardInfo($id){
        $response = $this->Peoplehub->requestData('get', 'user', 'user-cards', $id);
        if(!$response){
            $this->logout();
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

   public function forgotPassword(){
        $data = $this->request->data;
        $data['ref'] = $this->_host;
        $data = $this->_fireEvent('beforeForgotPassword',$data);
        $response = $this->Peoplehub->requestData('post', 'user', 'forgot_password', false, false, $data);
        $response = [$data,$response];
        $this->_fireEvent('forgotPassword',$response);
        $result['status'] = true;
        $result['message'] = "Result link generated successfully."; 
        $this->set('response', $result);
        $this->set('_serialize', 'response');
    }

    protected function _fireEvent($name, $data){
        $name = 'PeoplehubPatientApi.'.$name;
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

    public function viewVendor($id=null){
        $response = $this->_fireEvent('viewVendor', ['vendor_id' =>$id]);
        // pr($response); die;
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function resetPassword(){
        $response = $this->Peoplehub->requestData('post', 'user', 'reset_password', false, false, $this->request->data);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function redeemedCredits(){
        $this->_fireEvent('beforeRedemption', $this->request->data);
        $response = $this->Peoplehub->requestData('post', 'user', 'redeemedCredits', false, false, $this->request->data['phRedemptionData']);
        if(!$response){
            $this->logout();
        }
        $this->request->data['legacyRedemptionData']['transaction_number'] = $response->data->id;
        $this->request->data['legacyRedemptionData']['points'] = $response->data->transaction->points;
        $this->_fireEvent('afterRedemption', $this->request->data['legacyRedemptionData']);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function switchAccount(){
        $response = $this->Peoplehub->requestData('post', 'user', 'switch_account', false, false, $this->request->data);
        if(!$response){
          $this->logout();
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function editPatient($id){
       $response = $this->Peoplehub->requestData('put', 'user', 'users', $id, false, $this->request->data);
       if(!$response){
            $this->logout();
        }
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

    public function getPatientInfo($vendorPeoplehubId, $vendorId){
        
        $payload = ['vendor_id' => $vendorPeoplehubId];
        $response = $this->Peoplehub->requestData('get', 'user', 'me', false, false, $payload);
        
        if(!$response){
            $this->logout();
        }

        $eventData = [ 'vendor_id' => $vendorId, 'patient_id' => $response->data->id ];

        $afterGetPatientInfo = $this->_fireEvent('afterGetPatientInfo', $eventData);
        //If response of $aafterGetPatientInfo Event is an array then the keys of this array inserted into the data object of the response.
        if($afterGetPatientInfo && is_array($afterGetPatientInfo) && !empty($afterGetPatientInfo)){

            foreach ($afterGetPatientInfo as $key => $value) {
                $response->data->$key = $value;
            }
        }
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

    public function loginSocialUser(){
        $vendorId = $this->request->query('vendor_id');
        $provider = $this->request->query('provider');
        $this->_getVendorEndpoints($this->request->query('mode'));      
        return $this->redirect($this->_host.'/api/user/social-login?provider='.$provider.'&vendor_id='.$vendorId);
    }

    public function registerSocialUser(){
        $vendorId = $this->request->query('vendor_id');
        $provider = $this->request->query('provider');
        $card_number = $this->request->query('card_number');
        $this->_getVendorEndpoints($this->request->query('mode'));
        return $this->redirect($this->_host.'/api/user/social-signup?provider='.$provider.'&vendor_id='.$vendorId.'&card_number='.$card_number);
    }

    public function validateSocialLogin(){
        $headerData = ['BasicToken'=>$this->request->header('Authorization')];
        $response = $this->Peoplehub->requestData('post', 'user', 'social-login-verify', false, $headerData, false);
        $response = json_decode($response);
        $this->set('response', $response->data);
        $this->set('_serialize', 'response');
    }

    public function redeemProduct(){
       
       $data = $this->request->data;
       $response = $this->_fireEvent('redeemProduct', $data); 
       $this->set('response', $response);
       $this->set('_serialize', 'response');
    }

    public function LinkSocialAccount(){
        $vendorId = $this->request->query('vendor_id');
        $provider = $this->request->query('provider');
        $token  = $this->request->query('token');
        // pr($token); die('token here');
        $this->_getVendorEndpoints($this->request->query('mode'));      
        return $this->redirect($this->_host.'/api/user/social-link?provider='.$provider.'&vendor_id='.$vendorId.'&token='.$token);
    }

    public function unsubscribeEvent(){
        $response = $this->request->data;
        $response = $this->_fireEvent('unsubscribeEvent', $response);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function resendRewardLink(){
        $data = $this->request->data;
        $response = $this->Peoplehub->requestData('put', 'user', 'resend-reward', $data['transactionId'], false, false);
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function redeemGiftCoupon(){
        
        $redemptionType = [
            'store_credit' => 'redeemedCredits',
            'wallet_credit' => 'UserInstantRedemptions'
        ];

        $data = $this->request->data;
        
        $beforeRedeemEvent = $this->_fireEvent('beforeGiftCouponRedeem', $this->request->data);
        $response = $this->Peoplehub->requestData('post', 'user', $redemptionType[$beforeRedeemEvent['reward_type']], false, false, $beforeRedeemEvent);

        $data['transaction_number'] = $response->data->id;
        $data['redeemer_peoplehub_identifier'] = $response->data->transaction->user_id;
        
        $this->_fireEvent('afterGiftCouponRedeem', $data);
        
        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }

    public function errorReport(){

        $response = $this->_fireEvent('errorReport', $this->request->data);
        $this->set('response', $response);
        $this->set('_serialize', 'response');   
    }

    public function securityQuestions(){
        $response = $this->Peoplehub->requestData('get', 'user', 'security_questions', false, false);
        if(!$response){
            $this->logout();
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response'); 
    }
    
    public function setSecurityQuestions(){
        $response = $this->Peoplehub->requestData('post', 'user', 'set_security_question', false, false, $this->request->data);
        
        if(!$response){
            $this->logout();
        }

        $this->set('response', $response);
        $this->set('_serialize', 'response'); 
    }

    public function manageSecurityQuestions(){
        // pr($this->request->data); die;
        $response = $this->Peoplehub->requestData('put', 'user', 'manage_security_questions', false, false, $this->request->data);
        
        if(!$response){
            $this->logout();
        }

        $this->set('response', $response);
        $this->set('_serialize', 'response'); 
    }

    public function getSecurityQuestions(){
        $response = $this->Peoplehub->requestData('post', 'user', 'get_user_security_questions', false, false, $this->request->data);

        $this->set('response', $response);
        $this->set('_serialize', 'response'); 
    }

    public function responses(){
        $response = $this->Peoplehub->requestData('post', 'user', 'check_responses', false, false, $this->request->data);
        if(!$response){
          $this->logout();
        }
        $this->set('response', $response);
        $this->set('_serialize', 'response'); 
    }
}

//(folowing api's working fine: registerPatient, loginPatient, forgotPassword)

