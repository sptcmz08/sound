<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('component_product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 18, 4);
            $table->timestamps();
            $table->unique(['parent_product_id', 'component_product_id']);
        });
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->unique();
            $table->string('request_type')->index();
            $table->string('status')->default('PENDING')->index();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('target_product_id')->nullable()->constrained('products')->restrictOnDelete();
            $table->decimal('target_quantity', 18, 4)->nullable();
            $table->string('department_name')->nullable();
            $table->string('purpose');
            $table->text('note')->nullable();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('requested_at');
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->longText('approval_signature')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 18, 4);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['requisition_id', 'product_id']);
        });
        Schema::create('requisition_stock_documents', function (Blueprint $table) {
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_document_id')->constrained()->restrictOnDelete();
            $table->primary(['requisition_id', 'stock_document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_stock_documents');
        Schema::dropIfExists('requisition_items');
        Schema::dropIfExists('requisitions');
        Schema::dropIfExists('product_components');
    }
};
