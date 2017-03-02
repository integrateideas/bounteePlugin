<?php
namespace Integrateideas\Peoplehub\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PeoplehubUsers Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $PeoplehubUsers
 * @property \Cake\ORM\Association\HasMany $PeoplehubUsers
 *
 * @method \Integrateideas\Peoplehub\Model\Entity\PeoplehubUser get($primaryKey, $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\PeoplehubUser newEntity($data = null, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\PeoplehubUser[] newEntities(array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\PeoplehubUser|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\PeoplehubUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\PeoplehubUser[] patchEntities($entities, array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\PeoplehubUser findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PeoplehubUsersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('peoplehub_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => 'Integrateideas/Peoplehub.Users'
        ]);
        $this->belongsTo('PeoplehubUsers', [
            'foreignKey' => 'peoplehub_user_id',
            'joinType' => 'INNER',
            'className' => 'Integrateideas/Peoplehub.PeoplehubUsers'
        ]);
        $this->hasMany('PeoplehubUsers', [
            'foreignKey' => 'peoplehub_user_id',
            'className' => 'Integrateideas/Peoplehub.PeoplehubUsers'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['peoplehub_user_id'], 'PeoplehubUsers'));

        return $rules;
    }
}
