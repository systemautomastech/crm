<?php

namespace Automas\Taskly\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Automas\Taskly\Models\BugStage;
use Automas\Taskly\Models\TaskStage;

class TasklyDatabaseSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $this->call(PermissionTableSeeder::class);
        $this->call(MarketplaceSettingSeeder::class);
        $this->call(EmailTemplatesSeeder::class);
        $this->call(NotificationsTableSeeder::class);
    }
}
