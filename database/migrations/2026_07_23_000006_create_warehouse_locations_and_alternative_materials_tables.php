<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['warehouse_id', 'code']);
        });

        Schema::create('alternative_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('primary_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('alternative_product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('conversion_factor', 12, 4)->default(1.0000);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'primary_product_id', 'alternative_product_id'], 'alt_mat_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('warehouse_location_id')->nullable()->after('warehouse_id')->constrained('warehouse_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['warehouse_location_id']);
            $table->dropColumn('warehouse_location_id');
        });

        Schema::dropIfExists('alternative_materials');
        Schema::dropIfExists('warehouse_locations');
    }
};
