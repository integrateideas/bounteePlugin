<?php
namespace Integrateideas\Peoplehub\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Network\Exception\BadRequestException;
use Cake\Log\Log;
use Cake\I18n\Time;
use Cake\Network\Session;   
use Cake\Network\Http\Client;

/**
 * Peoplehub component
 */
class PeoplehubComponent extends Component
{

    const PEOPLEHUB_URL = "http://peoplehub.twinspark.co/peoplehub/api";
    private $_endpoint = null;
    private  $_session = null;
    private $_clientId = null;
    private $_clientSecret = null;
    private $_userType= null; //reseller or vendor or user

    public function initialize(array $config)
    {
        $this->_clientId = $config['clientId'];
        $this->_clientSecret = $config['clientSecret'];
        $this->_userType = $config['userType'];
        $this->_endpoint = self::BOUNTEE_URL."/";
        $this->_session = new Session();
    }

    private $_resourcesWithIdentifier = [                        
                                            'get'=>[
                                            'reseller'=>['vendors'],
                                            'user' => ['me', 'activities', 'user-cards'],
                                            'vendor'=>['users', 'rewardCredits', 'user-search', 'me', 'activities', 'UserInstantRedemptions']
                                            ],
                                            'put'=>[
                                            'reseller'=>['vendors'],
                                            'user' => ['switch_account', 'users'],
                                            'vendor'=>['users', 'vendors']
                                            ],
                                            'delete'=>[
                                            'reseller'=>['vendors'],
                                            ]
                                        ];
    private $_resourcesWithoutIdentifier = [                        

                                                'post' => [
                                                'reseller'=>['token', 'vendors'],

                                                'user' => ['login', 'register', 'logout', 'user-cards', 'forget_password', 'redeemedCredits','reset_password'],

                                                'vendor'=>['token', 'add-user', 'rewardCredits', 'UserInstantRedemptions', 'suggest_username']
                                                ]
                                            ];

    private function _validateResourceAndSubResource($httpMethod,$identifier,$resource,$subResource){
            if(!empty($resource) && !array_key_exists($resource, $this->$identifier[$httpMethod])){
                throw new Exception(__("Resource Name is missing or mispelled. The available options are ".implode(", ", array_keys($this->$identifier[$httpMethod]))));
          }
          if (!empty($subResource) && !in_array($subResource, $this->$identifier[$httpMethod][$resource])) {
            throw new Exception(__("Incorrect Subresource provided or mispelled. The available options for ".$resource." are ".implode(", ", $this->$identifier[$httpMethod][$resource])));
        }  
    }


    private function _validateInfo($httpMethod,$resource,$subResource){
        if(array_key_exists($httpMethod, $this->_resourcesWithIdentifier)){ 
            $this->_validateResourceAndSubResource($httpMethod,'_resourcesWithIdentifier',$resource,$subResource);
        }else if(array_key_exists($httpMethod, $this->_resourcesWithoutIdentifier)){
            $this->_validateResourceAndSubResource($httpMethod,'_resourcesWithoutIdentifier',$resource,$subResource); 
        }else{
            throw new Exception(__("Request method is mispelled")); 
        }

    }

    private function _createUrl($resource, $subResource, $subResourceId = false)
    {
        return $this->_endpoint . (($subResourceId) ? $resource."/".$subResource."/".$subResourceId  : $resource."/".$subResource);
    }

    private function _renewToken($httpMethod,$resource,$subResource,$subResourceId=false,$headerData=false,$payload=false)
    {
        $httpMethod = strtolower($httpMethod);
        $http = new Client();
        $url = $this->_createUrl($resource, $subResource, $subResourceId = false);
        // pr($url); die;
        if($resource == 'reseller' && $subResource == 'token'){
            $response = $http->$httpMethod($url, [], [
                'headers' => ['Authorization' => 'Basic '.base64_encode($this->_clientId.':'.$this->_clientSecret)]
                ]);
        }else if($resource == 'vendor' && $subResource == 'token'){
            $response = $http->$httpMethod($url, json_encode($payload), [
                'headers' => ['Authorization' => 'Basic '.base64_encode($this->_clientId.':'.$this->_clientSecret)]
                ]);
        }
        else if($resource == 'user' && $subResource == 'login'){
            $response = $http->$httpMethod($url, [], [
                'headers' => ['Authorization' => 'Basic '.base64_encode($headerData['username'].':'.$headerData['password'])]
                ]);
        }
        return $response;

    }

    private function _getToken($httpMethod,$resource,$subResource,$subResourceId=false,$payload=false)
    {   
        $this->_validateInfo($httpMethod,$resource,$subResource);
        $readToken = $this->_session->read('t');
        $isRenewRequired = false;
        if(!$readToken){
            $isRenewRequired = true;
        }else{
            $expireToken = (isset($readToken->expires))?$readToken->expires:null;
            $expireTime = date("H:i:s",strtotime($expireToken));
            $currentTime = Time::now();
            $currentTime = date("H:i:s",strtotime($currentTime));    
            if($expireTime <= $currentTime){
                $isRenewRequired = true;
            }
        }
        if($isRenewRequired){
            $response = $this->_renewToken($httpMethod,$resource,$subResource,$subResourceId,$headerData = false, $payload);
            if($response->isOk()){
                $response = json_decode($response->body());
                if($response->status){
                    $this->_session->write('t', $response->data);
                    $token = $response->data->token;
                }else{
                    Log::write('debug', 'Unable get '.$resource.' token');
                }
            }
        }else{
            $token = $readToken->token;
        }
        return 'Bearer '.$token;

    }

    public function requestData($httpMethod,$resource, $subResource, $subResourceId = false, $headerData = false, $payload=false,$vendorId = null)
    {
            //call renewToken function
        if($resource == 'user'){
            $token = $this->_getToken('post','user','login',$subResourceId=false, false);   
        }else if($resource == 'reseller'){
            $token = $this->_getToken('post','reseller','token',$subResourceId=false, false);
        }else if($resource == 'vendor'){
            $token = $this->_getToken('post','vendor','token',$subResourceId=false,['vendor_id' => $vendorId]);
        }else{
            Log::write('debug', 'Resource name is missing or mispelled');
        }
        $httpMethod = strtolower($httpMethod);
        $http = new Client();
        $this->_validateInfo($httpMethod,$resource,$subResource);
        $url = $this->_createUrl($resource, $subResource, $subResourceId = false);
        if($httpMethod == 'get'){
            if($payload){
                   $newurl = $url.'?'.http_build_query($payload);                 
               }else{
                   $newurl = $url;
               }
               // pr($newurl); die;
        $response = $http->$httpMethod($newurl, [], [
                'headers' => ['Authorization' => $token]]);
        // pr($response); die;
       }else{
        $response = $http->$httpMethod($url, json_encode($payload), [
            'headers' => ['Authorization' => $token]]);
    }
    if($response->isOk()){
        $response = json_decode($response->body());
        if($response->status){
            $response = [
            'status' => true,
            'data' => $response->data
            ];
        }
        return $response;
    }
  }
}
