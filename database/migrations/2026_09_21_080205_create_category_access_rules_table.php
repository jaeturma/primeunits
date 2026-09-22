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
        Schema::create('category_access_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('default_marketplace_tier')->default('regular');
            $table->string('min_buyer_access')->default('regular');
            $table->string('min_seller_access')->default('regular');
            $table->boolean('manual_review_required')->default(false);
            $table->boolean('public_preview_allowed')->default(true);
            $table->boolean('verified_buyer_required')->default(false);
            $table->boolean('ownership_documents_required')->default(false);
            $table->boolean('category_credentials_required')->default(false);
            $table->boolean('proof_of_funds_allowed')->default(false);
            $table->boolean('confidentiality_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_access_rules');
    }
};
