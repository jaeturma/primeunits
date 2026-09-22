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
        Schema::table('landing_ads', function (Blueprint $table) {
            $table->timestamp('starts_at')->nullable()->after('sort_order');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->string('target_marketplace_mode')->nullable()->after('ends_at');
            $table->string('review_status')->default('approved')->after('target_marketplace_mode');
            $table->boolean('show_in_feed')->default(false)->after('review_status');
            $table->unsignedInteger('impressions_count')->default(0)->after('show_in_feed');
            $table->unsignedInteger('clicks_count')->default(0)->after('impressions_count');

            $table->index(['show_in_feed', 'is_active', 'review_status'], 'landing_ads_feed_eligibility_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_ads', function (Blueprint $table) {
            $table->dropIndex('landing_ads_feed_eligibility_idx');
            $table->dropColumn([
                'starts_at',
                'ends_at',
                'target_marketplace_mode',
                'review_status',
                'show_in_feed',
                'impressions_count',
                'clicks_count',
            ]);
        });
    }
};
