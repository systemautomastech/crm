<?php

namespace Automas\Account\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $notifications = [
            'Customer Payment',
            'Vendor Payment',
            'Debit Note Approval',
            'Credit Note Approval'
        ];
        $permissions = [
            'cleared-customer-payments',
            'cleared-vendor-payments',
            'approve-debit-notes',
            'approve-credit-notes'
        ];
        foreach($notifications as $key=>$n){
            $ntfy = Notification::where('action',$n)->where('type','mail')->where('module','Account')->count();
            if($ntfy == 0){
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'Account';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}