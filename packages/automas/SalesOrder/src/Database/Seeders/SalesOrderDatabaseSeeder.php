<?php

namespace Automas\SalesOrder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SalesOrderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(PermissionTableSeeder::class);
        $this->call(NotificationsTableSeeder::class);
        $this->call(EmailTemplatesSeeder::class);
        $this->call(MarketplaceSettingSeeder::class);
    }
}

