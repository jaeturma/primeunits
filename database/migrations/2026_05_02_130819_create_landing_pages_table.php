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
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('hero_badge')->default('Verified sellers across Philippine regions');
            $table->string('hero_title');
            $table->text('hero_subtitle');
            $table->string('search_title')->default('Find your unit');
            $table->string('featured_title')->default('Featured listings');
            $table->string('featured_subtitle')->default('Location-aware results from verified sellers');
            $table->string('results_title')->default('Search results');
            $table->string('results_subtitle')->default('Filtered listings stay on this page for buyer and guest browsing');
            $table->string('budget_title')->default('Browse by budget');
            $table->string('seller_cta_title');
            $table->text('seller_cta_body');
            $table->string('seller_cta_button')->default('Apply as seller');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
