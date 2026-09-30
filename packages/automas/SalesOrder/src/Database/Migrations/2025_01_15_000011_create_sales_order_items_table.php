<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_order_items')) {
            Schema::create('sales_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->index()->constrained('sales_orders')->cascadeOnDelete();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_percentage', 8, 2)->default(0);
                $table->decimal('tax_percentage', 8, 2)->default(0);
                $table->decimal('final_price', 15, 2)->default(0);
                $table->string('unit')->nullable();
                $table->text('description')->nullable();
                $table->foreignId('creator_id')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};