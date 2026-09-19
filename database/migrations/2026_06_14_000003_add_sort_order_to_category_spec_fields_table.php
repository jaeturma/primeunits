<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_spec_fields', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('required');
            $table->boolean('is_searchable')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('category_spec_fields', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'is_searchable']);
        });
    }
};
