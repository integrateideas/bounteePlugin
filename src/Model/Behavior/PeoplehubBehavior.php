<?php

namespace Integrateideas\Peoplehub\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Table;

class PeoplehubBehavior extends Behavior
{
	/**
     * Default configuration.
     *
     * - field: the name of the datetime field to use for tracking `trashed` records.
     * - priority: the default priority for events
     * - events: the list of events to enable (also accepts arrays in `implementedEvents()`-compatible format)
     *
     * @var array
     */
    protected $_defaultConfig = [
        'userModel' => null,
        'field' => null,
        'pluginModelName'=>'Integrateideas/Peoplehub.Users',
        'pluginModelforeignKey'=> 'user_id',
        'events' => [
            'Model.beforeSave'
        ],
    ];

    public function initialize()
    {
       parent::initialize();
       $this->loadComponent('Integrateideas/Peoplehub.Peoplehub');
    }

    public function beforeSave(EntityInterface $entity, ArrayObject $object){
    	if($entity->isNew()){
    		//call peoplehub and fetch peoplehub id
    		$this->Peoplehub->requestData();
    		$this->loadModel('Users');
    		$this->Users->save($entity);
    	}
    }

}