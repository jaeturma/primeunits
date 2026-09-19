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
        Schema::table('category_spec_fields', function (Blueprint $table) {
            // `is_searchable` already exists (see the 2026_06_14_000003
            // migration); it was just never exposed on the model, so the
            // CategorySeeder's `is_searchable` values were silently
            // dropped on mass assignment. Fixed in the CategorySpecField
            // model instead of here. `is_classification` is genuinely new:
            // it flags the one spec field per category (body_type,
            // motorcycle_type, etc.) that represents its buyer-facing
            // classification, so search can filter on it directly instead
            // of guessing from a free-text match.
            $table->boolean('is_classification')->default(false)->after('is_searchable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_spec_fields', function (Blueprint $table) {
            $table->dropColumn(['is_classification']);
        });
    }
};
