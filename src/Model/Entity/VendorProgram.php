<?php
namespace Integrateideas\Peoplehub\Model\Entity;

use Cake\ORM\Entity;
use App\AuditStashPersister\Traits\AuditLogTrait;


/**
 * VendorProgram Entity
 *
 * @property int $id
 * @property int $client_id
 * @property int $peoplehub_vendor_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \Integrateideas\Peoplehub\Model\Entity\Client $client
 * @property \Integrateideas\Peoplehub\Model\Entity\PeoplehubVendor $peoplehub_vendor
 */
class VendorProgram extends Entity
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
