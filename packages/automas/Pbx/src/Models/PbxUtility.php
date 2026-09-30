<?php

namespace Automas\Pbx\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PbxUtility extends Model
{
    public static function defaultdata($company_id = null)
    {
        if (!empty($company_id)) {
            $setting = PbxSetting::where('created_by', $company_id)->first();

            if (empty($setting)) {
                $setting = new PbxSetting();
                $setting->pbx_name = 'Automas PBX';
                $setting->ami_port = 5038;
                $setting->extension_start = 100;
                $setting->extension_end = 199;
                $setting->max_extensions = 50;
                $setting->is_enabled = false;
                $setting->creator_id = $company_id;
                $setting->created_by = $company_id;
                $setting->save();
            }
        }
    }

    public static function GivePermissionToRoles($role_id = null, $rolename = null)
    {
        $staff_permission = [
            'manage extensions',
            'view extensions',
            'view call logs',
            'use dialer',
        ];

        $manager_permission = [
            'manage settings',
            'manage extensions',
            'view extensions',
            'create extensions',
            'edit extensions',
            'delete extensions',
            'manage pbx',
            'view call logs',
            'delete call logs',
            'use dialer',
        ];

        if ($rolename == 'staff') {
            $roles_v = Role::where('name', 'staff')
                ->where('id', $role_id)
                ->first();

            if (!empty($roles_v)) {
                foreach ($staff_permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)
                        ->where('module', 'Pbx')
                        ->first();

                    if (!empty($permission)) {
                        if (!$roles_v->hasPermissionTo($permission_v)) {
                            $roles_v->givePermissionTo($permission);
                        }
                    }
                }
            }
        }

        if ($rolename == 'hr') {
            $roles_v = Role::where('name', 'hr')
                ->where('id', $role_id)
                ->first();

            if (!empty($roles_v)) {
                foreach ($manager_permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)
                        ->where('module', 'Pbx')
                        ->first();

                    if (!empty($permission)) {
                        if (!$roles_v->hasPermissionTo($permission_v)) {
                            $roles_v->givePermissionTo($permission);
                        }
                    }
                }
            }
        }

        if ($rolename == 'manager') {
            $roles_v = Role::where('name', 'manager')
                ->where('id', $role_id)
                ->first();

            if (!empty($roles_v)) {
                foreach ($manager_permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)
                        ->where('module', 'Pbx')
                        ->first();

                    if (!empty($permission)) {
                        if (!$roles_v->hasPermissionTo($permission_v)) {
                            $roles_v->givePermissionTo($permission);
                        }
                    }
                }
            }
        }
    }
}
