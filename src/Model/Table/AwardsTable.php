<?php
namespace Integrateideas\Peoplehub\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Awards Model
 *
 * @property \Cake\ORM\Association\BelongsTo $AwardTypes
 * @property \Cake\ORM\Association\BelongsTo $PeoplehubTransactions
 *
 * @method \Integrateideas\Peoplehub\Model\Entity\Award get($primaryKey, $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\Award newEntity($data = null, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\Award[] newEntities(array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\Award|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\Award patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\Award[] patchEntities($entities, array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\Award findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AwardsTable extends Table
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

        $this->setTable('awards');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('AwardTypes', [
            'foreignKey' => 'award_type_id',
            'joinType' => 'INNER',
            'className' => 'Integrateideas/Peoplehub.AwardTypes'
        ]);
        $this->belongsTo('PeoplehubTransactions', [
            'foreignKey' => 'peoplehub_transaction_id',
            'joinType' => 'INNER',
            'className' => 'Integrateideas/Peoplehub.PeoplehubTransactions'
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
        $rules->add($rules->existsIn(['award_type_id'], 'AwardTypes'));
        $rules->add($rules->existsIn(['peoplehub_transaction_id'], 'PeoplehubTransactions'));

        return $rules;
    }
}
