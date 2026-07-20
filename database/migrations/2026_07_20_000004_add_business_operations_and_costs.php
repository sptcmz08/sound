<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('standard_cost', 18, 4)->default(0)->after('minimum_stock');
            $table->decimal('sale_price', 18, 4)->default(0)->after('standard_cost');
            $table->boolean('is_consumable')->default(false)->after('sale_price');
        });
        Schema::table('stock_document_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 18, 4)->default(0)->after('quantity');
            $table->decimal('unit_price', 18, 4)->default(0)->after('unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('stock_document_items', fn (Blueprint $table) => $table->dropColumn(['unit_cost', 'unit_price']));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['standard_cost', 'sale_price', 'is_consumable']));
    }
};
