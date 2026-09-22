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
        Schema::table('dealer_profiles', function (Blueprint $table) {
            $table->string('store_tier')->default('regular')->after('status')->index();
            $table->string('store_tier_status')->default('active')->after('store_tier')->index();
            $table->text('store_tier_notes')->nullable()->after('store_tier_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealer_profiles', function (Blueprint $table) {
            $table->dropColumn(['store_tier', 'store_tier_status', 'store_tier_notes']);
        });
    }
};
