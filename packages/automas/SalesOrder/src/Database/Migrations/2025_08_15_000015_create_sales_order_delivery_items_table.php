<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_order_delivery_items')) {
            Schema::create('sales_order_delivery_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_id')->index()->constrained('sales_order_deliveries')->cascadeOnDelete();
                $table->foreignId('sales_order_item_id')->index()->constrained('sales_order_items')->cascadeOnDelete();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->integer('quantity');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_delivery_items');
    }
};
