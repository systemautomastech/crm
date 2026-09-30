<?php

namespace Automas\SalesOrder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class NotificationsTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        if (!class_exists(Notification::class)) {
            return;
        }

        $notifications = [
            'New Sales Order',
            'Sales Order Status Updated',
            'New Sales Order Delivery',
            'Sales Order Delivery Cancelled',
        ];

        $permissions = [
            'view-sales-orders',
            'view-sales-orders',
            'view-sales-order-deliveries',
            'view-sales-order-deliveries',
        ];

        foreach ($notifications as $key => $n) {
            $ntfy = Notification::where('action', $n)
                ->where('type', 'mail')
                ->where('module', 'SalesOrder')
                ->count();

            if ($ntfy == 0) {
                $new              = new Notification();
                $new->action      = $n;
                $new->status      = 'on';
                $new->permissions = $permissions[$key];
                $new->module      = 'SalesOrder';
                $new->type        = 'mail';
                $new->save();
            }
        }
    }
}
