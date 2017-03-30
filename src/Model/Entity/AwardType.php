<?php
namespace Integrateideas\Peoplehub\Model\Entity;

use Cake\ORM\Entity;
use App\AuditStashPersister\Traits\AuditLogTrait;

/**
 * AwardType Entity
 *
 * @property int $id
 * @property string $name
 *
 * @property \Integrateideas\Peoplehub\Model\Entity\Award[] $awards
 */
class AwardType extends Entity
{
    use AuditLogTrait;
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
