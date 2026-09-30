<?php

namespace Automas\Taskly\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $notifications = [
            'Create Project',
            'Project Task',
            'Project Assign to Client',
        ];
        $permissions = [
            'manage-project',
            'manage-project-task',
            'invite-project-client',
        ];
        foreach($notifications as $key=>$n){
            $ntfy = Notification::where('action',$n)->where('type','mail')->where('module','Taskly')->count();
            if($ntfy == 0){
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'Taskly';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}