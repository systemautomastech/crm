<?php

namespace Automas\BiometricAttendance\Database\Seeders;

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
            // BiometricAttendance
            ['name' => 'manage-biometric-attendance', 'module' => 'biometric-attendance', 'label' => 'Manage Biometric Attendance'],
            ['name' => 'sync-biometric-attendance', 'module' => 'biometric-attendance', 'label' => 'View Biometric Attendance'],
            ['name' => 'view-biometric-attendance', 'module' => 'biometric-attendance', 'label' => 'Create Biometric Attendance'],
            
            ['name' => 'manage-biometric-settings', 'module' => 'biometric-attendance', 'label' => 'Manage Biometric Settings'],
            ['name' => 'edit-biometric-settings', 'module' => 'setting', 'label' => 'Edit Biometric Settings'],
        ];

        $company_role = Role::where('name', 'company')->first();

        foreach ($permission as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'BiometricAttendance',
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