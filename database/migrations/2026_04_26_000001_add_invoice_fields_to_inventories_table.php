<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('inventories', 'document_number')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->string('document_number')->nullable()->after('reference_number');
            });
        }

        if (! Schema::hasColumn('inventories', 'invoice_date')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->date('invoice_date')->nullable()->after('document_number');
            });
        }

        if (! Schema::hasColumn('inventories', 'invoice_due_date')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->date('invoice_due_date')->nullable()->after('invoice_date');
            });
        }

        if (! Schema::hasColumn('inventories', 'invoice_status')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->string('invoice_status', 30)->nullable()->after('invoice_due_date');
            });
        }

        if (! Schema::hasColumn('inventories', 'h7_notified_at')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->timestamp('h7_notified_at')->nullable()->after('invoice_status');
            });
        }

        if (! Schema::hasColumn('inventories', 'overdue_notified_at')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->timestamp('overdue_notified_at')->nullable()->after('h7_notified_at');
            });
        }

        Schema::table('inventories', function (Blueprint $table) {
            $table->index(['type', 'source', 'invoice_status', 'invoice_due_date'], 'inventories_invoice_due_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex('inventories_invoice_due_lookup_idx');

            $table->dropColumn([
                'document_number',
                'invoice_date',
                'invoice_due_date',
                'invoice_status',
                'h7_notified_at',
                'overdue_notified_at',
            ]);
        });
    }
};
