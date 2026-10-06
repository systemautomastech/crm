<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('proposals')) {
            Schema::create('proposals', function (Blueprint $table) {
                $table->id();
                $table->string('proposal_number');
                $table->string('reference')->nullable();
                $table->string('subject')->nullable();
                $table->date('proposal_date');
                $table->date('due_date');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                $table->text('customer_address')->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('type')->default('product'); // product | service
                $table->boolean('is_recurring')->default(false);
                $table->boolean('is_prepaid')->default(false);
                $table->boolean('is_tax_enabled')->default(true);
                $table->string('otc_discount_type')->default('percentage');
                $table->decimal('otc_discount_value', 15, 2)->default(0);
                $table->string('mrc_discount_type')->default('percentage');
                $table->decimal('mrc_discount_value', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->string('status')->default('draft'); // draft | sent | accepted | rejected
                $table->boolean('converted_to_sales_order')->default(false);
                $table->unsignedBigInteger('sales_order_id')->nullable();
                $table->unsignedBigInteger('converted_to_invoice')->nullable();
                $table->unsignedBigInteger('converted_to_deal')->nullable();
                $table->string('payment_terms')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('creator_id')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->index()->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('converted_to_invoice')->references('id')->on('sales_invoices')->nullOnDelete();

                $table->index(['status', 'proposal_date']);
                $table->index('customer_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
