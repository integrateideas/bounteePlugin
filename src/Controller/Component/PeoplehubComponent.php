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

   private $_endpoint = null;
   private  $_session = null;
   private $_clientId = null;
   private $_clientSecret = null;
   private $_liveEndPointUrl =null;
   private $_errorMode = null;
   public function initialize(array $config)
   {
    $this->_clientId = $config['clientId'];
    $this->_clientSecret = $config['clientSecret'];
    $this->_endpoint = $config['apiEndPointHost']."/api/";
    $this->_liveEndPointUrl = $config['liveApiEndPointHost']."/api/";
    $this->_errorMode = ( isset($config['throwErrorMode']))?$config['throwErrorMode']:true;
    $this->_session = new Session();
        // pr($content = $this->_session->read('data')); die;
}

private $_resourcesWithIdentifier = [
'get'=>[
'reseller'=>['vendors', 'reseller-card-series','user-search','activities'],
'user' => ['me', 'activities', 'user-cards', 'security_questions'],
'vendor'=>['users', 'rewardCredits', 'user-search', 'me', 'activities', 'UserInstantRedemptions', 'vendor-card-series']
],
'put'=>[
'reseller'=>['vendors'],
'user' => ['users','resend-reward','manage_security_questions'],
'vendor'=>['users', 'vendors','resend-reward']
],
'delete'=>[
'reseller'=>['vendors'],
'vendor' => ['users']
]
];
private $_resourcesWithoutIdentifier = [

'post' => [
'reseller'=>['token', 'vendors', 'vendor-cards', 'reseller-card-series'],

'user' => ['login', 'register', 'logout', 'user-cards', 'forgot_password', 'switch_account', 'redeemedCredits','reset_password','social-login-verify', 'renewRefreshToken', 'UserInstantRedemptions','set_security_question', 'check_responses', 'get_user_security_questions'],

'vendor'=>['token', 'add-user', 'rewardCredits', 'UserInstantRedemptions', 'suggest_username', 'add-vendor-to-live', 'vendor-card-series', 'redeemedCredits', 'upload-users', 'bulk-reward','reverse-credit','lost-card','reduce-credit']
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
    if($subResource == 'add-vendor-to-live' || ($subResource == 'activities' && $resource=='reseller')){
        return $this->_liveEndPointUrl . (($subResourceId) ? $resource."/".$subResource."/".$subResourceId  : $resource."/".$subResource);
    }else{
        return $this->_endpoint . (($subResourceId) ? $resource."/".$subResource."/".$subResourceId  : $resource."/".$subResource);
    }

}

private function _renewToken($httpMethod,$resource,$subResource,$subResourceId=false,$headerData=false,$payload=false){
    // pr($this->request->clientIp());
    $httpMethod = strtolower($httpMethod);
    $http = new Client();
    $url = $this->_createUrl($resource, $subResource, $subResourceId = false);
    if($resource == 'reseller'){
        $response = $http->$httpMethod($url, [], [
            'headers' => ['Authorization' => 'Basic '.base64_encode($this->_clientId.':'.$this->_clientSecret),
                //'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base
            'Referer' => Configure::read('authorizeDotNet.redirectUrl'),
            'ClientIp' => $this->request->clientIp()]
            ]);
    }else if($resource == 'vendor'){
        $response = $http->$httpMethod($url, json_encode($payload), [
            'headers' => ['Authorization' => 'Basic '.base64_encode($this->_clientId.':'.$this->_clientSecret),
                //'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base
            'Referer' => Configure::read('authorizeDotNet.redirectUrl'),
            'ClientIp' => $this->request->clientIp()]
            ]);

    }
    else if($resource == 'user'){
        if(isset($headerData['username']) && isset($headerData['password'])){
            $response = $http->$httpMethod($url, [], [
                'headers' => ['Authorization' => 'Basic '.base64_encode($headerData['username'].':'.$headerData['password']),
                // 'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base
                'Referer' => Configure::read('authorizeDotNet.redirectUrl'),
                'ClientIp' => $this->request->clientIp(),
                'hashKey' => $this->request->header('r_t')]
                ]);
        }else{

                // pr($headerData);
            $response = $http->$httpMethod($url, [], [
                'headers' => ['Authorization' => $headerData,
                // 'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base
                'Referer' => Configure::read('authorizeDotNet.redirectUrl'),
                'ClientIp' => $this->request->clientIp(),
                'hashKey' => $this->request->header('r_t')]
                ]);
                // pr($response); die;
        }
    }

    return $response;
}


private function _getToken($httpMethod,$resource,$subResource,$subResourceId=false,$headerData = false,$payload=false){
 // pr('m here in get token'); die;
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
               // pr('m here when expired');
               $headerData = $readToken[0];
               $token = $this->_checkRefreshToken('post', 'user', 'renewRefreshToken', false, $headerData);
               // pr($token);
               return $token;
                 // $isRenewRequired = true;
           }else{
                // pr('m here when not expired'); // pr($token);
                $isRenewRequired = false;
                return $token;
            }
        }
    }else{
        $isRenewRequired = true;
    }
}else{
    $readToken = $this->_session->read('token');
            // pr($readToken); die;
    if(!$readToken){
        $isRenewRequired = true;
    }else{
        $expireToken = (isset($readToken->data->expires))?$readToken->data->expires:null;
        $expireTime = date("H:i:s",strtotime($expireToken));
        $currentTime = Time::now();
        $currentTime = date("H:i:s",strtotime($currentTime));
        if($expireTime <= $currentTime){
            $isRenewRequired = true;
        }else{
         $isRenewRequired = false;
         $token = $readToken;
         return $token;
     }
 }
}
if($isRenewRequired){
    $response = $this->_renewToken($httpMethod,$resource,$subResource,$subResourceId,$headerData, $payload);
     // pr($response->body()); die;
    if($response->isOk()){
        // pr('m here when ok');die;
        $response = json_decode($response->body());
        // pr($response); die;
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
return $response;
}

private function _getAccessTokenForSocialLogin($headerData){
    $url  = $this->_endpoint . "user/social-login-verify";
    $http = new Client();
    $response = $http->post($url,[], [
        'headers' => ['Authorization' => $headerData ,
                //'Referer' => $this->request->env('REQUEST_SCHEME').'://'.$this->request->env('SERVER_NAME').$this->request->base
        'Referer' => Configure::read('authorizeDotNet.redirectUrl')]
        ]);
    if($response->isOk()){
        return $response->body();
    } else {
        throw new Exception('Failed to exchange token');
    }
}


public function requestData($httpMethod,$resource, $subResource, $subResourceId = false, $headerData = false, $payload=false,$vendorId = null)
{
    // pr($this->request->header('HashKey')); die;
    $this->_validateInfo($httpMethod,$resource,$subResource);
    if($resource == 'user'){
        if($subResource != 'register' && $subResource != 'forgot_password' && $subResource != 'reset_password' && $subResource != 'get_user_security_questions' && $subResource != 'check_responses'){
            if($subResource == 'social-login-verify'){
                if(isset($headerData['BasicToken']) && !empty($headerData['BasicToken'])){
                 $headerData['Authorization'] = $headerData['BasicToken'];
                 unset($headerData['BasicToken']);
             }else{
                die('errror');
            }
            $response = $this->_getAccessTokenForSocialLogin($headerData['Authorization']);

        }else{
            $response = $this->_getToken('post','user','login', false, $headerData);
            if(isset($response->status) && !empty($response->status)){
                // pr('m here when status is set and value');
                $token = $response->data->token;
            }else if(isset($response->status) && empty($response->status)){
                // pr('m here when status value is null');
                return false;
            }else{
                $token = $response;
            }
        }
     }
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
       // pr($token);
if($subResource != 'token' && $subResource != 'login' && $subResource != 'social-login-verify' ){
    $token = isset($token) ? 'Bearer '.$token : null;
    return $this->_sendRequest($token,$httpMethod,$resource, $subResource, $subResourceId, $headerData, $payload,$vendorId);
}else{
  return $response;
}

}

private function _sendRequest($token, $httpMethod,$resource, $subResource, $subResourceId, $headerData, $payload, $vendorId){
    // pr($this->request->header('HashKey')); die;
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
        'headers' => ['Authorization' => $token,
        'hashKey' => $this->request->header('r_t')
        ]]);
 }else{
    if($subResource != 'register' && $subResource != 'forgot_password' && $subResource != 'reset_password' && $subResource != 'get_user_security_questions' && $subResource != 'check_responses'){

        $response = $http->$httpMethod($url, json_encode($payload), [
            'headers' => ['Authorization' => $token,
            'hashKey' => $this->request->header('r_t')
            ]]);
        // die('sss');
    }else{
        $response = $http->$httpMethod($url, json_encode($payload));
    }
}
// pr($response->body()); die;
if($response->isOk()){
    $response = json_decode($response->body());
    return $response;
}else{
    $res = $response;
    Configure::write('debug', 1);
    $response = json_decode($response->body());
    if($this->_errorMode){

        if(!isset($response->status)){
            throw new Exception($response->message, $response->code);
        }else{
            $response->error = json_encode($response->error);
            throw new Exception($response->error, $res->code);
        }
    }else{

        return $response;

    }
}
}

private function _checkRefreshToken($httpMethod, $resource, $subResource, $subResourceId, $headerData){
    $httpMethod = strtolower($httpMethod);
    $http = new Client();
    $url = $this->_createUrl($resource, $subResource);
    // pr($url);
    $response = $http->$httpMethod($url, [], [
        'headers' => ['Authorization' => 'Bearer '.$headerData,
        'hashKey' => $this->request->header('HashKey')
        ]]);
    if($response->isOk()){
        $response = json_decode($response->body());
        return $response;
    }else{
        return false;
    }
}
}
