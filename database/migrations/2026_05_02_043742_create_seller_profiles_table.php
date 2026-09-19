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
        Schema::create('seller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('seller_type');
            $table->string('business_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->text('full_address')->nullable();
            $table->string('permit_number')->nullable();
            $table->string('permit_file')->nullable();
            $table->string('accreditation')->nullable();
            $table->string('accreditation_file')->nullable();
            $table->string('representative_name')->nullable();
            $table->string('representative_contact')->nullable();
            $table->string('representative_id_file')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->string('valid_id_file')->nullable();
            $table->string('selfie_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_profiles');
    }
};
