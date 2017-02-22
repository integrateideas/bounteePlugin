<?php
namespace Integrateideas\BounteePlugin\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Network\Exception\BadRequestException;
use Cake\Log\Log;

/**
 * Bountee  component
 */
class BounteeComponent extends Component{

    const BOUNTEE_URL = "http://peoplehub.twinspark.co/peoplehub";
  
    private $_endpoint = '';

    //index is for activities
    private $_resources = [
                            'users' => ['me']
                          ];

    /*private $_resourcesPost = [
                                'users' => ['register']
                              ];*/

    public function initialize(array $config)
    {
        $this->_endpoint = self::BOUNTEE_URL."/";
    }


    private function _createUrl($resource, $id = false)
    {
        return $this->_endpoint . (($id) ? $resource."/".$id."/" : $resource);
    }


    public function fetchData($token, $resource, $id = false)
    {

        if (!$token || !$resource || !array_key_exists($resource, $this->_resources)) {
            throw new Exception(__("Resource Name or Token is missing or mispelled. The available options are ".implode(", ", array_keys($this->_resources))));
        }
          
        $url = $this->_createUrl($resource, $id);
        return $this->_fetchResponseData($url, $token);
    }


    private function _fetchResponseData($url, $token)
    {
        $http = new Client();
        $response = $http->get($url, [], [
        'headers' => ['Authorization' => 'Bearer '.$token]
        ]);

        if ($response->code != '200') {
            $errorCall = new AppError();
            //error call here
            $errorCall->_displayError($response->body);

            return false;
        }

        return json_decode($response->body);
    }
   
}