<?php

namespace Automas\SalesOrder\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SalesOrderUtility extends Model
{
    /**
     * Called from DataDefault listener when the SalesOrder module is activated
     * for a new company. Sets up default data if needed.
     */
    public static function defaultData(int $companyId = null): void
    {
        // Initialize default SO/DC numbering settings
        if ($companyId) {
            SalesOrderSetting::updateOrCreate(
                ['creator_id' => $companyId, 'option' => 'so_prefix'],
                ['value' => 'SO', 'created_by' => $companyId]
            );
            SalesOrderSetting::updateOrCreate(
                ['creator_id' => $companyId, 'option' => 'so_starting_number'],
                ['value' => '1', 'created_by' => $companyId]
            );
            SalesOrderSetting::updateOrCreate(
                ['creator_id' => $companyId, 'option' => 'dc_prefix'],
                ['value' => 'DC', 'created_by' => $companyId]
            );
            SalesOrderSetting::updateOrCreate(
                ['creator_id' => $companyId, 'option' => 'dc_starting_number'],
                ['value' => '1', 'created_by' => $companyId]
            );
        }
    }

    /**
     * Grant SalesOrder permissions to a newly-created role.
     */
    public static function GivePermissionToRoles(int $role_id = null, string $rolename = null): void
    {
        $staff_permissions = [
            'manage-sales-orders',
            'manage-own-sales-orders',
            'manage-any-sales-orders',
            'create-sales-orders',
            'view-sales-orders',
            'edit-sales-orders',
            'delete-sales-orders',
            'print-sales-orders',
            'manage-sales-order-deliveries',
            'create-sales-order-deliveries',
            'view-sales-order-deliveries',
            'cancel-sales-order-deliveries',
            'print-delivery-challans',
            'convert-quotation-to-sales-order',
            'manage-sales-order-settings',
        ];

        if ($rolename === 'staff') {
            $role = Role::where('name', 'staff')->where('id', $role_id)->first();
            if ($role) {
                foreach ($staff_permissions as $permName) {
                    $perm = Permission::where('name', $permName)->first();
                    if ($perm && !$role->hasPermissionTo($permName)) {
                        $role->givePermissionTo($perm);
                    }
                }
            }
        }
    }
}
