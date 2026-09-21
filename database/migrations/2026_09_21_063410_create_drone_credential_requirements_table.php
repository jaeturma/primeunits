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
        Schema::create('drone_credential_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('country')->nullable();
            $table->string('credential_type');
            $table->boolean('is_required')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'country', 'credential_type'], 'drone_credential_requirements_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drone_credential_requirements');
    }
};
