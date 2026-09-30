<?php

namespace Database\Seeders;

use App\Classes\Module;
use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use App\Models\AddOn;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserActiveModule;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class PackageSeeder extends Seeder
{
    public function run($userId = null): void
    {
        if (!Schema::hasTable('add_ons')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                // Ignore
            }

            if (!Schema::hasTable('add_ons')) {
                Schema::create('add_ons', function (Blueprint $table) {
                    $table->id();
                    $table->string('module');
                    $table->string('name');
                    $table->decimal('monthly_price', 8, 2)->default(0);
                    $table->decimal('yearly_price', 8, 2)->default(0);
                    $table->string('image')->nullable();
                    $table->boolean('is_enable')->default(false);
                    $table->boolean('for_admin')->default(false);
                    $table->string('package_name')->nullable();
                    $table->integer('priority')->default(0);
                    $table->timestamps();
                });
            }
        }

        if (empty($userId)) {
            $companyUser = User::where('email', 'company@automas.com')->first()
                ?? User::where('type', 'company')->first()
                ?? User::first();
            $userId = $companyUser ? $companyUser->id : null;
        }
        $path = base_path('packages/automas');
        $devPackagePath = \Illuminate\Support\Facades\File::directories($path);

        foreach ($devPackagePath as $package) {
            $filePath = $package . '/module.json';
            if (!file_exists($filePath)) {
                continue;
            }
            $jsonContent = file_get_contents($filePath);
            $data = json_decode($jsonContent, true);

            $addon = AddOn::where('module', $data['name'])->first();
            if (empty($addon)) {
                $addon = new AddOn();
                $addon->module = $data['name'];
                $addon->name = $data['alias'];
                $addon->monthly_price = $data['monthly_price'] ?? 0;
                $addon->yearly_price = $data['yearly_price'] ?? 0;
                $addon->package_name = $data['package_name'];
                $addon->is_enable = true;
                $addon->for_admin = $data['for_admin'] ?? false;
                $addon->priority = $data['priority'] ?? 0;
                $addon->save();
            }

            if (!empty($userId)) {
                $activePackage = UserActiveModule::where('module', $data['name'])->where('user_id', $userId)->first();
                if (empty($activePackage)) {
                    $activePackage = new UserActiveModule();
                    $activePackage->user_id = $userId;
                    $activePackage->module = $data['name'];
                    $activePackage->save();
                }
            }
        }

        $allEnabled = (new Module())->allEnabled();
        foreach ($allEnabled as $key => $value) {
            try {
                Artisan::call('package:seed', ['packageName' => $value]);
                if (isset($this->command) && $this->command) {
                    $this->command->info("{$value} Seeder Run Successfully!");
                }
            } catch (\Throwable $th) {
                if (isset($this->command) && $this->command) {
                    $this->command->error("Failed to seed package '{$value}': " . $th->getMessage());
                }
            }
        }

        // static assignPlan
        if (Schema::hasTable('plans')) {
            $plan = Plan::where('custom_plan', true)->first();
            $user = User::where('email', 'company@automas.com')->first() ?? User::where('type', 'company')->first();
            if ($plan && $user) {
                $user->active_plan = $plan->id;
                $user->plan_expire_date = date('Y-m-d', strtotime('+10 month'));
                $user->total_user = -1;
                $user->storage_limit = 50000000;
                $user->save();
            }
        }

        $user = User::where('email', 'company@automas.com')->first() ?? User::where('type', 'company')->first();
        if ($user) {
            $modules = UserActiveModule::where('user_id', $user->id)->pluck('module')->toArray();
            $modulesStr = implode(',', $modules);
            DefaultData::dispatch($user->id, $modulesStr);
            $client_role = Role::where('name', 'client')->where('created_by', $user->id)->first();
            $staff_role = Role::where('name', 'staff')->where('created_by', $user->id)->first();

            if (!empty($client_role)) {
                GivePermissionToRole::dispatch($client_role->id, 'client', $modulesStr);
            }
            if (!empty($staff_role)) {
                GivePermissionToRole::dispatch($staff_role->id, 'staff', $modulesStr);
            }
        }
    }
}
