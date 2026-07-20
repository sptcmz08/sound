<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Convert products with is_consumable = 1 to SUPPLY
        DB::table('products')->where('is_consumable', true)->update(['product_type' => 'SUPPLY']);

        // 2. Create product_option_groups table
        Schema::create('product_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Create product_option_items table
        Schema::create('product_option_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_group_id')->constrained('product_option_groups')->cascadeOnDelete();
            $table->foreignId('option_product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('additional_price', 18, 4)->default(0);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Create sale_item_options table
        Schema::create('sale_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_document_item_id')->constrained('stock_document_items')->cascadeOnDelete();
            $table->foreignId('product_option_item_id')->constrained('product_option_items')->restrictOnDelete();
            $table->decimal('quantity', 18, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_options');
        Schema::dropIfExists('product_option_items');
        Schema::dropIfExists('product_option_groups');

        // Roll back SUPPLY type back to PART with is_consumable = 1
        DB::table('products')->where('product_type', 'SUPPLY')->update([
            'product_type' => 'PART',
            'is_consumable' => true
        ]);
    }
};
