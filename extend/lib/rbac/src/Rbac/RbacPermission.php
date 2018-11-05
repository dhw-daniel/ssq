<?php
namespace lib\rbac\src\Rbac;

use lib\rbac\src\Rbac\Contracts\RbacPermissionInterface;
use lib\rbac\src\Rbac\Traits\RbacPermission as RbacPermissionTraits;
use think\Model;

class RbacPermission extends Model implements RbacPermissionInterface
{
    use RbacPermissionTraits;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('rbac.permissions_table');
    }
}
