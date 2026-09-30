<?php

namespace Automas\Inventory\Database\Seeders;

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
            // Inventory Items
            ['name' => 'manage-inventory-items', 'module' => 'inventory-items', 'label' => 'Manage Inventory Items'],
            ['name' => 'manage-any-inventory-items', 'module' => 'inventory-items', 'label' => 'Manage All Inventory Items'],
            ['name' => 'manage-own-inventory-items', 'module' => 'inventory-items', 'label' => 'Manage Own Inventory Items'],
            ['name' => 'view-inventory-items', 'module' => 'inventory-items', 'label' => 'View Inventory Items'],

            // Inventory Adjustments
            ['name' => 'manage-inventory-adjustments', 'module' => 'inventory-adjustments', 'label' => 'Manage Inventory Adjustments'],
            ['name' => 'manage-any-inventory-adjustments', 'module' => 'inventory-adjustments', 'label' => 'Manage All Inventory Adjustments'],
            ['name' => 'manage-own-inventory-adjustments', 'module' => 'inventory-adjustments', 'label' => 'Manage Own Inventory Adjustments'],
            ['name' => 'view-inventory-adjustments', 'module' => 'inventory-adjustments', 'label' => 'View Inventory Adjustments'],
            ['name' => 'create-inventory-adjustments', 'module' => 'inventory-adjustments', 'label' => 'Create Inventory Adjustments'],
            ['name' => 'delete-inventory-adjustments', 'module' => 'inventory-adjustments', 'label' => 'Delete Inventory Adjustments'],
            ['name' => 'approve-inventory-adjustments', 'module' => 'inventory-adjustments', 'label' => 'Approve Inventory Adjustments'],

            // Inventory Reports
            ['name' => 'manage-inventory-reports', 'module' => 'inventory-reports', 'label' => 'Manage Inventory Reports'],
            ['name' => 'view-inventory-reports', 'module' => 'inventory-reports', 'label' => 'View Inventory Reports'],
        ];

        $company_role = Role::where('name', 'company')->first();

        foreach ($permission as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'Inventory',
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
