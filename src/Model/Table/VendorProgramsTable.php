<?php
namespace Integrateideas\Peoplehub\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\AuditStashPersister\Traits\AuditLogTrait;
/**
 * VendorPrograms Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Clients
 * @property \Cake\ORM\Association\BelongsTo $PeoplehubVendors
 *
 * @method \Integrateideas\Peoplehub\Model\Entity\VendorProgram get($primaryKey, $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\VendorProgram newEntity($data = null, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\VendorProgram[] newEntities(array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\VendorProgram|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\VendorProgram patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\VendorProgram[] patchEntities($entities, array $data, array $options = [])
 * @method \Integrateideas\Peoplehub\Model\Entity\VendorProgram findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class VendorProgramsTable extends Table
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

        $this->table('vendor_programs');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('AuditStash.AuditLog');

        $this->belongsTo('Clients', [
            'foreignKey' => 'client_id',
            'joinType' => 'INNER',
            'className' => 'Integrateideas/Peoplehub.Clients'
        ]);
        $this->belongsTo('PeoplehubVendors', [
            'foreignKey' => 'peoplehub_vendor_id',
            'joinType' => 'INNER',
            'className' => 'Integrateideas/Peoplehub.PeoplehubVendors'
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
        $rules->add($rules->existsIn(['client_id'], 'Clients'));
        $rules->add($rules->existsIn(['peoplehub_vendor_id'], 'PeoplehubVendors'));

        return $rules;
    }
}
