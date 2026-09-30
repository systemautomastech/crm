<?php

namespace Automas\Pbx\Listeners;

use App\Events\GivePermissionToRole;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Automas\Pbx\Models\PbxUtility;

class GiveRoleToPermission
{
    public function handle(GivePermissionToRole $event)
    {
        $role_id = $event->role_id;
        $rolename = $event->rolename;
        $user_module = $event->user_module ? explode(',', $event->user_module) : [];
        if (!empty($user_module)) {
            if (in_array("Pbx", $user_module)) {
                PbxUtility::GivePermissionToRoles($role_id, $rolename);
            }
        }
    }
}