<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_orders')) {
            Schema::create('sales_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->nullable();
                $table->string('name');

                // Loose reference to quotations and proposals (no FK constraint — standalone)
                $table->unsignedBigInteger('quotation_id')->nullable()->index();
                $table->unsignedBigInteger('proposal_id')->nullable()->index();

                // Status
                $table->string('status')->default('draft');       // draft | confirmed | cancelled
                $table->string('delivery_status')->default('pending'); // pending | partial | delivered

                // Assignment & Group
                $table->string('assignment_status')->default('unassigned')->index(); // unassigned | group_assigned | acquired
                $table->unsignedBigInteger('assigned_group_id')->nullable()->index();
                $table->unsignedBigInteger('acquired_by')->nullable()->index();
                $table->timestamp('acquired_at')->nullable();

                // Customer & Warehouse (FK to users/warehouses from core)
                $table->foreignId('customer_id')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->index()->constrained('warehouses')->nullOnDelete();

                // Dates
                $table->date('order_date');
                $table->date('expected_delivery_date')->nullable();
                $table->timestamp('confirmed_at')->nullable();

                // Addresses
                $table->json('billing_address')->nullable();
                $table->json('shipping_address')->nullable();

                // Financials
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);

                // Notes / Terms
                $table->text('description')->nullable();
                $table->text('notes')->nullable();

                // Invoice tracking
                $table->boolean('is_invoiced')->default(false);
                $table->unsignedBigInteger('invoice_id')->nullable()->index();

                // Ownership
                $table->foreignId('creator_id')->nullable()->index()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();

                $table->timestamps();

                // Composite indexes for tenant queries
                $table->index(['created_by', 'status']);
                $table->index(['created_by', 'order_number']);
                $table->index(['created_by', 'customer_id']);
                $table->index(['created_by', 'delivery_status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
