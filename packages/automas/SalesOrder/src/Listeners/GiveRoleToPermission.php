<?php

namespace Automas\SalesOrder\Listeners;

use App\Events\GivePermissionToRole;
use Automas\SalesOrder\Models\SalesOrderUtility;

class GiveRoleToPermission
{
    public function __construct() {}

    public function handle(GivePermissionToRole $event): void
    {
        $roleId   = $event->role_id;
        $roleName = $event->rolename;
        $modules  = $event->user_module ? explode(',', $event->user_module) : [];

        if (!empty($modules) && in_array('SalesOrder', $modules)) {
            SalesOrderUtility::GivePermissionToRoles($roleId, $roleName);
        }
    }
}