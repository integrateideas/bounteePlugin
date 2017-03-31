<?php
namespace Integrateideas\Peoplehub\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Log\Log;
use Cake\I18n\Time;
use Cake\Network\Session;   
use Cake\Network\Http\Client;

/**
 * Peoplehub component
 */
class PeoplehubComponent extends Component
{

 const PEOPLEHUB_URL = "http://peoplehub.twinspark.co/bountee_dev/api";
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
        $this->_endpoint = self::PEOPLEHUB_URL."/";
        $this->_session = new Session();
        // pr($content = $this->_session->read('data')); die;
    }

    private $_resourcesWithIdentifier = [                        
                                            'get'=>[
                                            'reseller'=>['vendors', 'reseller-card-series'],
                                            'user' => ['me', 'activities', 'user-cards'],
                                            'vendor'=>['users', 'rewardCredits', 'user-search', 'me', 'activities', 'UserInstantRedemptions', 'vendor-card-series']
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
                                            'reseller'=>['token', 'vendors', 'vendor-cards', 'reseller-card-series'],

                                            'user' => ['login', 'register', 'logout', 'user-cards', 'forgot_password', 'redeemedCredits','reset_password', 'fb-login'],

                                            'vendor'=>['token', 'add-user', 'rewardCredits', 'UserInstantRedemptions', 'suggest_username', 'add-vendor-to-live', 'vendor-card-series', 'redeemedCredits', 'upload-users']
                                            ]
                                          ];

    private function _validateResourceAndSubResource($httpMethod,$identifier,$resource,$subResource){
        $attribute = $this->$identifier;
        if(!empty($resource) && !array_key_exists($resource, $attribute[$httpMethod])){
            throw new Exception(__("Resource Name is missing or mispelled. The available options are ".implode(", ", array_keys($attribute[$httpMethod]))));
        }
        if (!empty($subResource) && !in_array($subResource, $attribute[$httpMethod][$resource])) {
            throw new Exception(__("Incorrect Subresource provided or mispelled. The available options for ".$resource." are ".implode(", ", $attribute[$httpMethod][$resource])));
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

    private function _renewToken($httpMethod,$resource,$subResource,$subResourceId=false,$headerData=false,$payload=false){
        // pr($headerData); die;
        $httpMethod = strtolower($httpMethod);
        $http = new Client();
        $url = $this->_createUrl($resource, $subResource, $subResourceId = false);

        if($resource == 'reseller'){
            $response = $http->$httpMethod($url, [], [
                'headers' => ['Authorization' => 'Basic '.base64_encode($this->_clientId.':'.$this->_clientSecret), 'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base]
                ]);
        }else if($resource == 'vendor'){
            $response = $http->$httpMethod($url, json_encode($payload), [
                'headers' => ['Authorization' => 'Basic '.base64_encode($this->_clientId.':'.$this->_clientSecret), 'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base]
                ]);
        }
        else if($resource == 'user'){
            if(isset($headerData['username']) && isset($headerData['password'])){
                $response = $http->$httpMethod($url, [], [
                'headers' => ['Authorization' => 'Basic '.base64_encode($headerData['username'].':'.$headerData['password']), 'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base]
                ]);
            }else{

                // pr($headerData);
                $response = $http->$httpMethod($url, [], [
                'headers' => ['Authorization' => $headerData, 'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base]
                ]);
                // pr($response);
            }
            
        }
        // pr($response->body()); die;
        return $response;
    }


    private function _getToken($httpMethod,$resource,$subResource,$subResourceId=false,$headerData = false,$payload=false){

        $isRenewRequired = false;
        if($resource == 'user'){
            if($this->request->header('Authorization')){
                $token = $this->request->header('Authorization');
                $token = str_replace('Bearer ', '', $token);
                $readToken = unserialize($this->_session->read($token));
                if($readToken){
                   //now check if token is expired or not. if expired set $isRrenewRequired = true;  
                   $expireToken = (isset($readToken[1]))?$readToken[1]:null;
                   $expireTime = date("H:i:s",strtotime($expireToken));
                   // pr($expireTime); 
                   $currentTime = Time::now();
                   $currentTime = date("H:i:s",strtotime($currentTime));
                   // pr($currentTime); die;
                   if($expireTime <= $currentTime){
                     $headerData = $readToken[0];
                     $isRenewRequired = true;
                   }else{
                            // pr($token);
                        $isRenewRequired = false;
                        return $token;
                   }
               } 
            }else{
                $isRenewRequired = true;
            }
        }else{
            $readToken = $this->_session->read('token');
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
        }
        if($isRenewRequired){
            // pr('is renew is true');
            $response = $this->_renewToken($httpMethod,$resource,$subResource,$subResourceId,$headerData, $payload);
            if($response->isOk()){
                $response = json_decode($response->body());
                if($response->status){
                    if($resource == 'user'){
                        if(isset($headerData['username']) && isset($headerData['password'])){
                            $this->_session->write($response->data->token, serialize(['Basic '.base64_encode($headerData['username'].':'.$headerData['password']), $response->data->expires]));
                        }else{
                            // pr(' m here when no username');
                            $this->_session->write($response->data->token, serialize([$headerData, $response->data->expires]));
                            // pr($this->_session->read(unserialize($response->data->token))); die;
                        }                       
                    }else{
                         $this->_session->write('token', $response);
                    }
                }else{
                    $err =array();
                    $err['status']=false;
                    $err['data']['message']='Unable to get '.$resource.' token.';
                    $err['data']['data']=json_decode($response->body());
                    return $err;
                }
            }
        }else{
            // pr(' m here when not expired'); die;
            $response = $token;
        }
        // pr($response); die;
        return $response;
    }

    public function requestData($httpMethod,$resource, $subResource, $subResourceId = false, $headerData = false, $payload=false,$vendorId = null)
    {
        $this->_validateInfo($httpMethod,$resource,$subResource);

        if($resource == 'user'){
            if($subResource != 'register' && $subResource != 'forgot_password'){
              $response = $this->_getToken('post','user','login', false, $headerData);
              if(isset($response->status)){
                $token = $response->data->token;               
              }else{
                $token = $response;
              }
            }
        // pr($token); die;
       }else if($resource == 'reseller'){
            $response = $this->_getToken('post','reseller','token');
            $token = $response->data->token;
       }else if($resource == 'vendor'){
            $response = $this->_getToken('post','vendor','token', false, false, ['vendor_id' => $vendorId]);
            $token = $response->data->token;
       }else{
            throw new Exception("Resource name is mispelled or not found");

       }
       // pr('in request data method');
       // pr($token); die;
       if($subResource != 'token' && $subResource != 'login'){
            $token = isset($token) ? 'Bearer '.$token : null;
            return $this->_sendRequest($token,$httpMethod,$resource, $subResource, $subResourceId, $headerData, $payload,$vendorId);
       }else{
          return $response;
       }

    }

    private function _sendRequest($token, $httpMethod,$resource, $subResource, $subResourceId, $headerData, $payload, $vendorId){
        $httpMethod = strtolower($httpMethod);
        $http = new Client();
        $url = $this->_createUrl($resource, $subResource, $subResourceId);
        if($httpMethod == 'get'){
            if($payload){
                // pr(' m here when payload');
               $newurl = $url.'?'.http_build_query($payload); 
               // pr($newurl); die;                
           }else{
               $newurl = $url;
           }
           $response = $http->$httpMethod($newurl, [], [
            'headers' => ['Authorization' => $token]]);
       }else{
        if($subResource != 'register' && $subResource != 'forgot_password'){

            $response = $http->$httpMethod($url, json_encode($payload), [
                'headers' => ['Authorization' => $token]]);           
        }else{
            $response = $http->$httpMethod($url, json_encode($payload)); 
        }
    }
    if($response->isOk()){
        $response = json_decode($response->body());
        return $response;
    }else{
        $res = $response;
        $response = json_decode($response->body());
        if(!isset($response->status)){
            throw new Exception($response->message, $response->code); 
        }else{
            $response->error = json_encode($response->error);
            throw new Exception($response->error, $res->code); 
        }        
    }
}
}