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
            $table->string('marketplace_tier')->default('regular')->after('category_id')->index();
            $table->string('visibility_level')->default('public')->after('marketplace_tier')->index();
            $table->string('seller_capacity')->nullable()->after('visibility_level');
            $table->boolean('is_gold_candidate')->default(false)->after('seller_capacity');
            $table->boolean('confidentiality_required')->default(false)->after('is_gold_candidate');
            $table->text('public_preview_summary')->nullable()->after('confidentiality_required');
            $table->string('registration_number')->nullable()->after('public_preview_summary');
            $table->boolean('price_on_request')->default(false)->after('registration_number');

            $table->index(['marketplace_tier', 'visibility_level'], 'listings_tier_visibility_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'marketplace_tier',
                'visibility_level',
                'seller_capacity',
                'is_gold_candidate',
                'confidentiality_required',
                'public_preview_summary',
                'registration_number',
                'price_on_request',
            ]);
        });
    }
};
