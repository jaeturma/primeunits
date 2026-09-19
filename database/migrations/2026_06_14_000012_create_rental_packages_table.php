<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_unit_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('duration_type');
            $table->unsignedInteger('duration_value')->default(1);
            $table->decimal('price', 10, 2);
            $table->text('inclusions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_packages');
    }
};
