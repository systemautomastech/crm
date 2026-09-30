<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pos_returns')) {
            Schema::create('pos_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number')->unique();
                $table->date('return_date');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->unsignedBigInteger('original_pos_id');
                $table->text('reason')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->enum('status', ['draft', 'approved', 'completed', 'cancelled'])->default('draft');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('creator_id');
                $table->integer('created_by');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_returns');
    }
};
