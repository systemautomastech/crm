<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pos_discount_products')) {
            Schema::create('pos_discount_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pos_discount_id')->constrained('pos_discounts')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('product_service_items')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['pos_discount_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_discount_products');
    }
};
