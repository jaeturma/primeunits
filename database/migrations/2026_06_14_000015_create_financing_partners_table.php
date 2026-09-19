<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financing_partners', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('slug')->unique();
            $table->string('registration_number')->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_file')->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_number');
            $table->string('contact_email')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->text('full_address')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financing_partners');
    }
};
