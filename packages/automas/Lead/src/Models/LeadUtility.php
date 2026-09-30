<?php

namespace Automas\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Automas\Lead\Models\Pipeline;
use Automas\Lead\Models\LeadStage;
use Automas\Lead\Models\DealStage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LeadUtility extends Model
{
    public static function defaultdata($company_id = null)
    {
        // Stopped automatic seeding of sales pipeline and stages during system updates/module initialization.
    }

    public static function GivePermissionToRoles($role_id = null, $rolename = null)
    {
        $staff_permission = [
            'manage-crm-dashboard',
            'manage-leads',
            'manage-own-lead',
            'create-lead',
        ];

        $client_permission = [
            'manage-crm-dashboard',
            'manage-leads',
            'manage-own-lead',
            'view-leads',
            'manage-deals',
            'manage-own-deals',
            'view-deals',
            'create-deal-tasks',
            'edit-deal-tasks',
            'delete-deal-tasks',
            'manage-deal-tasks',
            'view-reports',
        ];

        if ($rolename == 'staff') {
            $roles_v = Role::where('name', 'staff')->where('id', $role_id)->first();
            foreach ($staff_permission as $permission_v) {
                $permission = Permission::where('name', $permission_v)->first();
                if (!empty($permission)) {
                    if (!$roles_v->hasPermissionTo($permission_v)) {
                        $roles_v->givePermissionTo($permission);
                    }
                }
            }
        }

        if ($rolename == 'client') {
            $roles_v = Role::where('name', 'client')->where('id', $role_id)->first();
            foreach ($client_permission as $permission_v) {
                $permission = Permission::where('name', $permission_v)->first();
                if (!empty($permission)) {
                    if (!$roles_v->hasPermissionTo($permission_v)) {
                        $roles_v->givePermissionTo($permission);
                    }
                }
            }
        }
    }
}
