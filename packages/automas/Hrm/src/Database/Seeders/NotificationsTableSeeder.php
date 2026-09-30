<?php

namespace Automas\Hrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $notifications = [
            'Create Award',
            'Promotions Approval',
            'Resignations Status',
            'Warning Approval',
            'Transfers Approval',
            'Leave Status',
            'Payroll Processed',
        ];
        $permissions = [
            'manage-awards',
            'manage-promotions-status',
            'manage-resignation-status',
            'manage-warning-response',
            'manage-employee-transfers-status',
            'manage-leave-status',
            'run-payrolls',
        ];
        foreach($notifications as $key=>$n){
            $ntfy = Notification::where('action',$n)->where('type','mail')->where('module','Hrm')->count();
            if($ntfy == 0){
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'Hrm';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}