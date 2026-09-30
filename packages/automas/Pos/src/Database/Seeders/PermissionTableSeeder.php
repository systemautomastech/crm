<?php

namespace Automas\Pos\Database\Seeders;

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
            ['name' => 'manage-pos-dashboard', 'module' => 'pos', 'label' => 'Manage Pos Dashboard'],

            ['name' => 'manage-pos', 'module' => 'pos', 'label' => 'Manage Pos'],
            ['name' => 'create-pos', 'module' => 'pos', 'label' => 'Create Pos'],

            ['name' => 'manage-pos-orders', 'module' => 'pos-orders', 'label' => 'Manage Pos Orders'],
            ['name' => 'view-pos-orders', 'module' => 'pos-orders', 'label' => 'View Pos Orders'],

            ['name' => 'manage-pos-barcodes', 'module' => 'pos-barcodes', 'label' => 'Manage Pos Barcodes'],
            ['name' => 'print-pos-barcodes', 'module' => 'pos-barcodes', 'label' => 'Print Pos Barcodes'],

            ['name' => 'manage-pos-reports', 'module' => 'pos-reports', 'label' => 'Manage Pos Reports'],
            ['name' => 'view-pos-reports', 'module' => 'pos-reports', 'label' => 'View Pos Reports'],

            ['name' => 'manage-pos-billing-counters', 'module' => 'pos-counters', 'label' => 'Manage Pos Billing Counters'],
            ['name' => 'create-pos-billing-counters', 'module' => 'pos-counters', 'label' => 'Create Pos Billing Counters'],
            ['name' => 'edit-pos-billing-counters', 'module' => 'pos-counters', 'label' => 'Edit Pos Billing Counters'],
            ['name' => 'view-pos-billing-counters', 'module' => 'pos-counters', 'label' => 'View Pos Billing Counters'],
            ['name' => 'delete-pos-billing-counters', 'module' => 'pos-counters', 'label' => 'Delete Pos Billing Counters'],

            ['name' => 'manage-pos-discounts', 'module' => 'pos-discounts', 'label' => 'Manage Pos Discounts'],
            ['name' => 'create-pos-discounts', 'module' => 'pos-discounts', 'label' => 'Create Pos Discounts'],
            ['name' => 'edit-pos-discounts', 'module' => 'pos-discounts', 'label' => 'Edit Pos Discounts'],
            ['name' => 'view-pos-discounts', 'module' => 'pos-discounts', 'label' => 'View Pos Discounts'],
            ['name' => 'delete-pos-discounts', 'module' => 'pos-discounts', 'label' => 'Delete Pos Discounts'],

            ['name' => 'manage-pos-returns', 'module' => 'pos-returns', 'label' => 'Manage Pos Returns'],
            ['name' => 'manage-any-pos-returns', 'module' => 'pos-returns', 'label' => 'Manage All Pos Returns'],
            ['name' => 'manage-own-pos-returns', 'module' => 'pos-returns', 'label' => 'Manage Own Pos Returns'],
            ['name' => 'create-pos-returns', 'module' => 'pos-returns', 'label' => 'Create Pos Returns'],
            ['name' => 'edit-pos-returns', 'module' => 'pos-returns', 'label' => 'Edit Pos Returns'],
            ['name' => 'view-pos-returns', 'module' => 'pos-returns', 'label' => 'View Pos Returns'],
            ['name' => 'delete-pos-returns', 'module' => 'pos-returns', 'label' => 'Delete Pos Returns'],
            ['name' => 'approve-pos-returns', 'module' => 'pos-returns', 'label' => 'Approve Pos Returns'],
            ['name' => 'complete-pos-returns', 'module' => 'pos-returns', 'label' => 'Complete Pos Returns'],
        ];

        $company_role = Role::where('name', 'company')->first();

        foreach ($permission as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'Pos',
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