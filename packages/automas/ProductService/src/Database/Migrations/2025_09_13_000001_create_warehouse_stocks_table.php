<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('warehouse_stocks')) {
            Schema::create('warehouse_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('product_service_items')->restrictOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
                $table->decimal('quantity', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};
