<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales_quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_quotations', 'customer_type')) {
                $table->string('customer_type')->nullable()->after('due_date');
            }
            if (Schema::hasColumn('sales_quotations', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->change();
            }
            if (!Schema::hasColumn('sales_quotations', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('sales_quotations', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('sales_quotations', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('sales_quotations', 'customer_address')) {
                $table->text('customer_address')->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('sales_quotations', 'is_recurring')) {
                $table->boolean('is_recurring')->nullable()->after('warehouse_id');
            }
            if (!Schema::hasColumn('sales_quotations', 'is_prepaid')) {
                $table->boolean('is_prepaid')->nullable()->after('is_recurring');
            }
            if (!Schema::hasColumn('sales_quotations', 'is_tax_enabled')) {
                $table->boolean('is_tax_enabled')->nullable()->after('is_prepaid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_quotations', function (Blueprint $table) {
            $columns = [];
            foreach (['customer_name', 'customer_email', 'customer_phone', 'customer_address'] as $col) {
                if (Schema::hasColumn('sales_quotations', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
