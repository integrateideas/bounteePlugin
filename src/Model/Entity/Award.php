<?php
namespace Integrateideas\Peoplehub\Model\Entity;

use Cake\ORM\Entity;

/**
 * Award Entity
 *
 * @property int $id
 * @property int $award_type_id
 * @property int $peoplehub_transaction_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \Integrateideas\Peoplehub\Model\Entity\AwardType $award_type
 * @property \Integrateideas\Peoplehub\Model\Entity\PeoplehubTransaction $peoplehub_transaction
 */
class Award extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
