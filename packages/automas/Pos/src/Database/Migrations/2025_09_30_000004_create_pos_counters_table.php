<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pos_billing_counters')) {
            Schema::create('pos_billing_counters', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code');
                $table->boolean('status')->default(true);
                $table->text('description')->nullable();
                $table->foreignId('bank_account_id')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_billing_counters');
    }
};
