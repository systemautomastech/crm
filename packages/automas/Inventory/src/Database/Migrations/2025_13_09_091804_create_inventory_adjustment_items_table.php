<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_adjustment_items')) {
            Schema::create('inventory_adjustment_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('adjustment_id');
                $table->unsignedBigInteger('item_id');
                $table->decimal('current_quantity', 15, 2)->default(0);
                $table->decimal('adjusted_quantity', 15, 2);
                $table->decimal('difference_quantity', 15, 2);  
                $table->text('notes')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();

                $table->foreign('adjustment_id')->references('id')->on('inventory_adjustments')->onDelete('cascade');
                $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('cascade');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_items');
    }
};
