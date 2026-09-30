<?php

namespace Automas\Hrm\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Automas\Hrm\Models\HrmUtility;

class GiveRoleToPermission
{
    public function __construct()
    {
        //
    }

    public function handle($event)
    {
        $role_id = $event->role_id;
        $rolename = $event->rolename;
        $user_module = $event->user_module ? explode(',', $event->user_module) : [];
        if (!empty($user_module)) {
            if (in_array("Hrm", $user_module)) {
                HrmUtility::GivePermissionToRoles($role_id, $rolename);
            }
        }
    }
}
