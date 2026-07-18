<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('VIEWER')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('must_change_password')->default(false);
        });
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->string('product_type')->index();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->decimal('minimum_stock', 18, 4)->default(0);
            $table->string('location_text')->nullable();
            $table->string('image_path')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix');
            $table->string('period');
            $table->unsignedBigInteger('current_number')->default(0);
            $table->unique(['prefix', 'period']);
            $table->timestamps();
        });
        Schema::create('stock_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_no')->unique();
            $table->string('document_type');
            $table->date('document_date');
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('reference_no')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('department_name')->nullable();
            $table->string('purpose')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('DRAFT')->index();
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('reversal_of_id')->nullable()->unique()->constrained('stock_documents')->restrictOnDelete();
            $table->timestamps();
            $table->index(['document_type', 'document_date']);
        });
        Schema::create('stock_document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 18, 4);
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['stock_document_id', 'product_id']);
        });
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
        });
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('transaction_uuid')->unique();
            $table->foreignId('stock_document_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_document_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('transaction_type')->index();
            $table->decimal('quantity_in', 18, 4)->default(0);
            $table->decimal('quantity_out', 18, 4)->default(0);
            $table->decimal('balance_after', 18, 4);
            $table->timestamp('occurred_at');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'warehouse_id', 'occurred_at']);
            $table->index('stock_document_id');
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('stock_document_items');
        Schema::dropIfExists('stock_documents');
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('units');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'is_active', 'last_login_at', 'must_change_password']));
    }
};
