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
        Schema::create('promotion_impressions', function (Blueprint $table) {
            $table->id();
            $table->string('promotion_type');
            $table->string('promotable_type');
            $table->unsignedBigInteger('promotable_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->string('marketplace_mode');
            $table->timestamp('viewed_at');

            $table->index(['promotable_type', 'promotable_id']);
            $table->index(['promotion_type', 'viewed_at']);
            // De-dupe window: the same session should not inflate impressions
            // for the same promoted item within the same hour bucket.
            $table->string('dedupe_key')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_impressions');
    }
};
