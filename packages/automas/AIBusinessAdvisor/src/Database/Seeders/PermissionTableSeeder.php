<?php

namespace Automas\AIBusinessAdvisor\Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
        Artisan::call('cache:clear');

        $superadminPermissions = [
            ['name' => 'manage-ai-business-advisor-settings', 'module' => 'ai-advisor-business-settings', 'label' => 'Manage AI Business Advisor Settings'],
        ];

        $companyPermissions = [
            ['name' => 'manage-ai-business-advisor', 'module' => 'ai-advisor-business', 'label' => 'Manage AI Business Advisor'],
            ['name' => 'manage-ai-business-adviser-status', 'module' => 'ai-advisor-business', 'label' => 'Manage AI Business Adviser Status'],
        ];

        $superadminRole = Role::where('name', 'superadmin')->first();
        $companyRole = Role::where('name', 'company')->first();

        // Assign superadmin permissions
        foreach ($superadminPermissions as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'AIBusinessAdvisor',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            if ($superadminRole && !$superadminRole->hasPermissionTo($permission_obj)) {
                $superadminRole->givePermissionTo($permission_obj);
            }
        }

        // Assign company permissions
        foreach ($companyPermissions as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'AIBusinessAdvisor',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            if ($companyRole && !$companyRole->hasPermissionTo($permission_obj)) {
                $companyRole->givePermissionTo($permission_obj);
            }
        }
    }
}
