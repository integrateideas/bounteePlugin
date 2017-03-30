<?php
namespace Integrateideas\Peoplehub\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\AuditStashPersister\Traits\AuditLogTrait;

/**
 * AwardTypes Model
 *
 * @property \Cake\ORM\Association\HasMany $Awards
 *
 * @method \Integrateideas\Peoplehub\Model\Entity\AwardType get($primaryKey, $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\AwardType newEntity($data = null, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\AwardType[] newEntities(array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\AwardType|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\AwardType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\AwardType[] patchEntities($entities, array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\AwardType findOrCreate($search, callable $callback = null, $options = [])
 */
class AwardTypesTable extends Table
{
    use AuditLogTrait;
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('award_types');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('AuditStash.AuditLog');

        $this->hasMany('Awards', [
            'foreignKey' => 'award_type_id',
            'className' => 'Integrateideas/Peoplehub.Awards'
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

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        return $validator;
    }
}
