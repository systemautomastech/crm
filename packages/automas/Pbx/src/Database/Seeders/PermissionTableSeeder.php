<?php

namespace Automas\Pbx\Database\Seeders;

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

        $permission = [
            ['name' => 'manage pbx', 'module' => 'pbx', 'label' => 'Manage PBX'],
            ['name' => 'manage-pbx-dashboard', 'module' => 'pbx', 'label' => 'Manage PBX Dashboard'],

            ['name' => 'manage extensions', 'module' => 'pbx', 'label' => 'Manage Extensions'],
            ['name' => 'view all extensions', 'module' => 'pbx', 'label' => 'View All Extensions'],
            ['name' => 'view own extensions', 'module' => 'pbx', 'label' => 'View Own Extensions'],
            ['name' => 'create extensions', 'module' => 'pbx', 'label' => 'Create Extensions'],
            ['name' => 'edit extensions', 'module' => 'pbx', 'label' => 'Edit Extensions'],
            ['name' => 'delete extensions', 'module' => 'pbx', 'label' => 'Delete Extensions'],

            ['name' => 'manage settings', 'module' => 'pbx', 'label' => 'Manage Settings'],
            ['name' => 'manage dialer settings', 'module' => 'pbx', 'label' => 'Manage Dialer Settings'],

            ['name' => 'manage call logs', 'module' => 'pbx', 'label' => 'Manage Call Logs'],
            ['name' => 'view all call logs', 'module' => 'pbx', 'label' => 'View All Call Logs'],
            ['name' => 'view own call logs', 'module' => 'pbx', 'label' => 'View Own Call Logs'],
            ['name' => 'delete call logs', 'module' => 'pbx', 'label' => 'Delete Call Logs'],

            ['name' => 'use dialer', 'module' => 'pbx', 'label' => 'Use Dialer'],
        ];

        $company_role = Role::where('name', 'company')->first();

        foreach ($permission as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'Pbx',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            if ($company_role && !$company_role->hasPermissionTo($permission_obj)) {
                $company_role->givePermissionTo($permission_obj);
            }
        }
    }
}
