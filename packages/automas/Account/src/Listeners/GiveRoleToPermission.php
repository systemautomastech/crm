<?php

namespace Automas\Account\Listeners;

use App\Events\GivePermissionToRole;
use Automas\Account\Helpers\AccountUtility;

class GiveRoleToPermission
{
    public function __construct()
    {
        //
    }

    public function handle(GivePermissionToRole $event)
    {
        $role_id = $event->role_id;
        $rolename = $event->rolename;
        $user_module = $event->user_module ? explode(',', $event->user_module) : [];
        if (!empty($user_module)) {
            if (in_array("Account", $user_module)) {
                AccountUtility::GivePermissionToRoles($role_id, $rolename);
            }
        }
    }
}
