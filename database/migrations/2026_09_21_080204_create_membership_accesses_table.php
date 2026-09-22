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
        Schema::create('membership_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('identity_verification_level')->default('none');
            $table->string('buyer_access_level')->default('none')->index();
            $table->string('buyer_access_status')->default('active');
            $table->string('seller_access_level')->default('none')->index();
            $table->string('seller_access_status')->default('active');
            $table->boolean('is_restricted')->default(false);
            $table->text('restricted_reason')->nullable();
            $table->string('current_mode')->default('regular');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_accesses');
    }
};
