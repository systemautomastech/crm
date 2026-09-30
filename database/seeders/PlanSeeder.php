<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('type','superadmin')->first();

        if (Plan::where('created_by', $admin->id)->exists()) {
            return;
        }

        $plans = [
            [
                'name' => 'Custom Plan',
                'description' => 'Tailored solution for specific business needs',
                'number_of_users' => 0,
                'custom_plan' => true,
                'status' => true,
                'free_plan' => false,
                'modules' => [],
                'package_price_yearly' => 0,
                'package_price_monthly' => 0,
                'trial' => false,
                'trial_days' => 0,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Free Plan',
                'description' => 'Perfect for getting started with basic features',
                'number_of_users' => 10,
                'custom_plan' => false,
                'status' => true,
                'free_plan' => true,
                'modules' => ["Taskly","Account","Hrm","Lead","Pos","Stripe","Paypal"],
                'package_price_yearly' => 0,
                'package_price_monthly' => 0,
                'trial' => false,
                'trial_days' => 0,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Starter Plan',
                'description' => 'Great for small teams and growing businesses',
                'number_of_users' => 50,
                'custom_plan' => false,
                'status' => true,
                'free_plan' => false,
                'modules' => ["Taskly","Account","Hrm","Lead","Pos","Stripe","Paypal"],
                'package_price_yearly' => 240,
                'package_price_monthly' => 25,
                'trial' => true,
                'trial_days' => 14,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Professional Plan',
                'description' => 'Advanced features for established businesses',
                'number_of_users' => 100,
                'custom_plan' => false,
                'status' => true,
                'free_plan' => false,
                'modules' => ["Taskly","Account","Hrm","Lead","Pos","Stripe","Paypal"],
                'package_price_yearly' => 960,
                'package_price_monthly' => 99,
                'trial' => true,
                'trial_days' => 30,
                'created_by' => $admin->id,
            ],
        ];

        $plan = Plan::first();
        if (!$plan) {
            foreach ($plans as $plan) {
                Plan::firstOrCreate(
                    ['name' => $plan['name']],
                    $plan
                );
            }
        }
    }
}
