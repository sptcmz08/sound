<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('stock_documents', 'qc_status')) {
            Schema::table('stock_documents', function (Blueprint $table) {
                $table->string('qc_status', 20)->default('PASSED')->after('status');
                $table->string('qc_note', 255)->nullable()->after('qc_status');
                $table->foreignId('inspected_by')->nullable()->after('qc_note')->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('requisitions', 'qc_status')) {
            Schema::table('requisitions', function (Blueprint $table) {
                $table->string('qc_status', 20)->default('PASSED')->after('status');
                $table->string('qc_note', 255)->nullable()->after('qc_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stock_documents', 'qc_status')) {
            Schema::table('stock_documents', function (Blueprint $table) {
                $table->dropForeign(['inspected_by']);
                $table->dropColumn(['qc_status', 'qc_note', 'inspected_by']);
            });
        }

        if (Schema::hasColumn('requisitions', 'qc_status')) {
            Schema::table('requisitions', function (Blueprint $table) {
                $table->dropColumn(['qc_status', 'qc_note']);
            });
        }
    }
};
