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
        Schema::table('listings', function (Blueprint $table) {
            $table->index('created_at', 'listings_created_at_analytics_index');
            $table->index(['status', 'created_at'], 'listings_status_created_at_analytics_index');
            $table->index(['category_id', 'status', 'created_at'], 'listings_category_status_created_at_analytics_index');
            $table->index(['region', 'province'], 'listings_region_province_analytics_index');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('created_at', 'leads_created_at_analytics_index');
            $table->index(['status', 'created_at'], 'leads_status_created_at_analytics_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('created_at', 'transactions_created_at_analytics_index');
            $table->index(['status', 'created_at'], 'transactions_status_created_at_analytics_index');
        });

        Schema::table('commission_logs', function (Blueprint $table) {
            $table->index('created_at', 'commission_logs_created_at_analytics_index');
            $table->index(['status', 'created_at'], 'commission_logs_status_created_at_analytics_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('created_at', 'payments_created_at_analytics_index');
            $table->index(['status', 'created_at'], 'payments_status_created_at_analytics_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_created_at_analytics_index');
            $table->dropIndex('payments_created_at_analytics_index');
        });

        Schema::table('commission_logs', function (Blueprint $table) {
            $table->dropIndex('commission_logs_status_created_at_analytics_index');
            $table->dropIndex('commission_logs_created_at_analytics_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_status_created_at_analytics_index');
            $table->dropIndex('transactions_created_at_analytics_index');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_status_created_at_analytics_index');
            $table->dropIndex('leads_created_at_analytics_index');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('listings_region_province_analytics_index');
            $table->dropIndex('listings_category_status_created_at_analytics_index');
            $table->dropIndex('listings_status_created_at_analytics_index');
            $table->dropIndex('listings_created_at_analytics_index');
        });
    }
};
