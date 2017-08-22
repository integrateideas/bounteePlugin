<?php

namespace Integrateideas\Peoplehub\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Network\Exception\InternalErrorException;

class PeoplehubBehavior extends Behavior
{
	/**
     * Default configuration.
     *
     * - peopleHubObject: the name of the model of the plugin use for saving the records.
     * - peopleHubObjectId: the name of the plugin field associated with the people hub model.
     * - peopleHubObjectforeignKey: the name of the plugin field which is associated with the application model
     *
     * @var array
     */
    protected $_defaultConfig = [
        'peopleHubObject'=>null,
        'peopleHubObjectId'=> null,
        'peopleHubObjectforeignKey' => null,
        'events' => 'Model.afterSave'
    ];

    public function initialize(array $config)
    {
       
       if($config['peopleHubObject'] == 'VendorPrograms'){
        $config['peopleHubObjectId'] = 'peoplehub_vendor_id';
       }
       if($config['peopleHubObject'] == 'PeoplehubUsers'){
        $config['peopleHubObjectId'] = 'peoplehub_user_id';
       }
       if($config['peopleHubObject'] == 'Awards'){
        $config['peopleHubObjectId'] = 'peoplehub_transaction_id';
       }
       $this->_config = $config;
       $component = new Controller;
       $this->Peoplehub = $component->loadComponent('Integrateideas/Peoplehub.Peoplehub', [ 
        'clientId' => Configure::read('Peoplehub.clientId'),
        'clientSecret' =>Configure::read('Peoplehub.clientSecret'),
        'userType' => Configure::read('Peoplehub.userType'),
	'apiEndPointHost' =>Configure::read('Peoplehub.apiEndPointHost'),
        'liveApiEndPointHost' =>Configure::read('Peoplehub.liveApiEndPointHost'),
      ]);
       $this->Model = $component->loadModel($this->_config['peopleHubObject']);
    }

    public function afterSave($entity, $options){
        $httpMethod = $options->options['httpMethod'];
        $resource = $options->options['resource'];
        $subResource = $options->options['subResource'];
        $subResourceId = $options->options['subResourceId'];
        $headerData = $options->options['headerData'];
        $vendorId = $options->options['vendorId'];
        $payload = $options->options['payload'];
        //call peoplehub and fetch peoplehub id
        $response = $this->Peoplehub->requestData($httpMethod, $resource, $subResource, $subResourceId, $headerData, $payload, $vendorId);
        if(!$response || $response['status']!=true){
            $message ="";
            throw new InternalErrorException(__($message));     
        }else{
            // if($config['peopleHubObject'] == 'Awards'){
            //     pr(' m here'); die;
            // }
            $entity = [
                        $this->_config['peopleHubObjectId'] => $response['data']->id,
                        $this->_config['peopleHubObjectforeignKey'] => $options->id
                      ];
            $this->Model->addBehavior('Timestamp'); 
            $data = $this->Model->newEntity();
            $data = $this->Model->patchEntity($data, $entity);
            if($this->Model->save($data)){
                $data =array();
                $data['status']=true;
            }else{
                throw new InternalErrorException(__('Internal Error'));
            }
    	}
    }
}
