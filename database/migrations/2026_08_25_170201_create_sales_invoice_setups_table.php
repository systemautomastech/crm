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
        if (!Schema::hasTable('sales_invoice_setups')) {
            Schema::create('sales_invoice_setups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_id')->nullable()->index();
                $table->string('option');
                $table->longText('value')->nullable();
                $table->timestamps();

                $table->unique(['creator_id', 'option']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_setups');
    }
};
