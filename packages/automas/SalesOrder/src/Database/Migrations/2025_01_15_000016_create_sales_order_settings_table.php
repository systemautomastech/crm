<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_order_settings')) {
            Schema::create('sales_order_settings', function (Blueprint $table) {
                $table->id();
                $table->string('option');
                $table->text('value')->nullable();
                $table->foreignId('creator_id')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['creator_id', 'option']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_settings');
    }
};
