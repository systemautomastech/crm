<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('project_payments')) {
            Schema::create('project_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number');
                $table->date('payment_date');
                $table->date('due_date');
                $table->foreignId('project_id')->index();
                $table->foreignId('customer_id')->nullable()->index();
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('balance_amount', 10, 2)->default(0);
                $table->foreignId('bank_account_id')->nullable();
                $table->enum('status', ['draft', 'posted'])->default('draft');
                $table->text('payment_terms')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->integer('created_by')->nullable()->index();

                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
                $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
                $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_payments');
    }
};
