<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_cogs_history')) {
            Schema::create('inventory_cogs_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->date('sale_date');
                $table->decimal('quantity_sold', 15, 2);
                $table->decimal('unit_cost', 15, 2);
                $table->decimal('total_cogs', 15, 2);
                $table->string('reference_type');
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();

                $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('cascade');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_cogs_history');
    }
};
