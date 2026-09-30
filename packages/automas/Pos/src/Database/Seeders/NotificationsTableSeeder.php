<?php

namespace Automas\Pos\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $notifications = [
            'Create POS',
            'POS Return'
        ];
        $permissions = [
            'manage-pos',
            'manage-pos-returns'
        ];
        foreach($notifications as $key=>$n){
            $ntfy = Notification::where('action',$n)->where('type','mail')->where('module','Pos')->count();
            if($ntfy == 0){
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'Pos';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}