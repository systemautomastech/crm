<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_order_deliveries')) {
            Schema::create('sales_order_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_order_id')->index()->constrained('sales_orders')->cascadeOnDelete();
                $table->string('delivery_number')->nullable();
                $table->date('delivery_date');
                $table->text('notes')->nullable();
                $table->string('status')->default('delivered'); // delivered | cancelled
                $table->foreignId('creator_id')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['created_by', 'status']);
                $table->index(['sales_order_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_deliveries');
    }
};
