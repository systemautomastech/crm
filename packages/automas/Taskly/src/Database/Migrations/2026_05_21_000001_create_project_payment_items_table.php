<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('project_payment_items')) {
            Schema::create('project_payment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->index();
                $table->foreignId('milestone_id')->index();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->foreignId('creator_id')->nullable()->index();
                $table->integer('created_by')->nullable()->index();

                $table->foreign('payment_id')->references('id')->on('project_payments')->onDelete('cascade');
                $table->foreign('milestone_id')->references('id')->on('project_milestones')->onDelete('cascade');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_payment_items');
    }
};
