<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_units', function (Blueprint $table) {
            $table->boolean('with_driver')->default(true)->after('capacity');
        });
    }

    public function down(): void
    {
        Schema::table('rental_units', function (Blueprint $table) {
            $table->dropColumn('with_driver');
        });
    }
};
