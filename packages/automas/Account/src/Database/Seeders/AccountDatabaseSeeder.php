<?php

namespace Automas\Account\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Automas\Account\Helpers\AccountUtility;
use Automas\Account\Models\BankAccount;
use Automas\Account\Models\Customer;
use Automas\Account\Models\Expense;
use Automas\Account\Models\ExpenseCategories;
use Automas\Account\Models\Revenue;
use Automas\Account\Models\RevenueCategories;
use Automas\Account\Models\Vendor;

class AccountDatabaseSeeder extends Seeder
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
