<?php

namespace Automas\SmartReports\Database\Seeders;

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
            ['name' => 'manage-smart-reports', 'module' => 'smart-reports', 'label' => 'Manage Smart Reports'],
            
            ['name' => 'manage-attendance-reports', 'module' => 'smart-reports', 'label' => 'Manage Attendance Reports'],
            ['name' => 'manage-payroll-reports', 'module' => 'smart-reports', 'label' => 'Manage Payroll Reports'],
            ['name' => 'manage-leave-reports', 'module' => 'smart-reports', 'label' => 'Manage Leave Reports'],
            ['name' => 'manage-sales-invoice-reports', 'module' => 'smart-reports', 'label' => 'Manage Sales Invoice Reports'],
            ['name' => 'manage-purchase-invoice-reports', 'module' => 'smart-reports', 'label' => 'Manage Purchase Invoice Reports'],
            ['name' => 'manage-deal-pipeline-reports', 'module' => 'smart-reports', 'label' => 'Manage Deal Pipeline Reports'],
            ['name' => 'manage-product-stock-reports', 'module' => 'smart-reports', 'label' => 'Manage Product Stock Reports'],
            ['name' => 'manage-project-progress-reports', 'module' => 'smart-reports', 'label' => 'Manage Project Progress Reports'],

           
        ];

        $company_role = Role::where('name', 'company')->first();

        foreach ($permission as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'SmartReports',
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