<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_unit_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_available')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['rental_unit_id', 'date']);
            $table->index(['rental_unit_id', 'date', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_availabilities');
    }
};
