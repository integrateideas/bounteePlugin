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

    const BOUNTEE_URL = "http://peoplehub.twinspark.co/peoplehub/api";

    private $_endpoint = '';

    //index is for activities
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
                                            'vendor'=>[]
                                            ]
                                            ];
    private $_resourcesWithoutIdentifier = [                        
                                            'post'=>[
                                            'reseller'=>['token', 'vendors'],
                                            'user' => ['login', 'register', 'logout', 'user-cards', 'forget_password', 'redeemedCredits', 'reset_password']
                                            ],
                                            'vendor'=>['token', 'add-user', 'rewardCredits', 'UserInstantRedemptions', 'suggest_username']
                                           ];

    /*private $_resourcesPost = [
    'users' => ['register']
    ];*/

    public function initialize(array $config)
    {
        $this->_endpoint = self::BOUNTEE_URL."/";
    }


    private function _createUrl($_resourcesWithIdentifier, $_resourcesWithoutIdentifier, $id = false, $subResource)
    {
        return $this->_endpoint . ($_resourcesWithIdentifier."/".$subResource."/".($id) ? $_resourcesWithIdentifier."/".$subResource."/".$id  : $_resourcesWithoutIdentifier."/".$subResource);
    }


}