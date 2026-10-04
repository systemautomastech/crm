<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if(!Schema::hasTable('sales_invoice_items'))
        {
            Schema::create('sales_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->unsignedBigInteger('product_id');
                $table->text('description')->nullable();
                $table->string('product_type')->nullable();
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->string('discount_type', 20)->default('percentage');
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('tax_percentage', 5, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->foreignId('creator_id')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('invoice_id')->references('id')->on('sales_invoices')->onDelete('cascade');

                $table->index('invoice_id');
                $table->index('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_items');
    }
};