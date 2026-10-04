<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_order_users')) {
            Schema::create('sales_order_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_order_id')->index()->constrained('sales_orders')->cascadeOnDelete();
                $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['sales_order_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_users');
    }
};
