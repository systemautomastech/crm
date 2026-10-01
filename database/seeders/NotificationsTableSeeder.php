<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NotificationsTableSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = [
            [
                'action' => 'New User',
                'module' => 'general',
                'permissions' => 'manage-users',
            ],
            [
                'action' => 'Plan Purchase',
                'module' => 'general',
                'permissions' => 'manage-email-plan-purchases',
            ],
            [
                'action' => 'Sales Invoice',
                'module' => 'general',
                'permissions' => 'manage-sales-invoices',
            ],
            [
                'action' => 'Sales Invoice Return',
                'module' => 'general',
                'permissions' => 'manage-sales-return-invoices',
            ],
            [
                'action' => 'Proposal Sent',
                'module' => 'general',
                'permissions' => 'sent-sales-proposals',
            ],
            [
                'action' => 'Proposal Approval',
                'module' => 'general',
                'permissions' => 'accept-sales-proposals',
            ],
            [
                'action' => 'Quotation Sent',
                'module' => 'Quotation',
                'permissions' => 'manage-quotations',
            ],
        ];

        $companyRole = Role::where('name', 'company')->first();

        foreach ($notifications as $item) {
            // Ensure permission exists and is granted to company role
            $perm = Permission::firstOrCreate(
                ['name' => $item['permissions'], 'guard_name' => 'web'],
                ['module' => $item['module'], 'label' => ucwords(str_replace('-', ' ', $item['permissions']))]
            );

            if ($companyRole && !$companyRole->hasPermissionTo($perm)) {
                $companyRole->givePermissionTo($perm);
            }

            // Create notification entry if not exists
            Notification::firstOrCreate(
                [
                    'action' => $item['action'],
                    'type' => 'mail',
                    'module' => $item['module'],
                ],
                [
                    'status' => 'on',
                    'permissions' => $item['permissions'],
                ]
            );
        }
    }
}
