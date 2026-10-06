<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');

        $permissions = [
            ['name' => 'manage-sales-proposals', 'module' => 'sales-proposals', 'label' => 'Manage Sales Proposals'],
            ['name' => 'create-sales-proposals', 'module' => 'sales-proposals', 'label' => 'Create Proposals'],
            ['name' => 'edit-sales-proposals', 'module' => 'sales-proposals', 'label' => 'Edit Sales Proposals'],
            ['name' => 'delete-sales-proposals', 'module' => 'sales-proposals', 'label' => 'Delete Sales Proposals'],
            ['name' => 'view-sales-proposals', 'module' => 'sales-proposals', 'label' => 'View Sales Proposals'],
            ['name' => 'sent-sales-proposals', 'module' => 'sales-proposals', 'label' => 'Sent Sales Proposals'],
            ['name' => 'accept-sales-proposals', 'module' => 'sales-proposals', 'label' => 'Accept Sales Proposals'],
            ['name' => 'reject-sales-proposals', 'module' => 'sales-proposals', 'label' => 'Reject Sales Proposals'],
            ['name' => 'convert-sales-proposals', 'module' => 'sales-proposals', 'label' => 'Convert Sales Proposals'],
            ['name' => 'manage-proposal-system-setup', 'module' => 'sales-proposals', 'label' => 'Manage Proposal Setup'],
        ];

        $roles = \Spatie\Permission\Models\Role::whereIn('name', ['company', 'admin', 'superadmin'])->get();

        foreach ($permissions as $perm) {
            $permissionObj = \Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'general',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            foreach ($roles as $role) {
                if (!$role->hasPermissionTo($permissionObj->name)) {
                    $role->givePermissionTo($permissionObj);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'manage-sales-proposals',
            'create-sales-proposals',
            'edit-sales-proposals',
            'delete-sales-proposals',
            'view-sales-proposals',
            'sent-sales-proposals',
            'accept-sales-proposals',
            'reject-sales-proposals',
            'convert-sales-proposals',
            'manage-proposal-system-setup',
        ];

        \Spatie\Permission\Models\Permission::whereIn('name', $permissions)->delete();
    }
};
