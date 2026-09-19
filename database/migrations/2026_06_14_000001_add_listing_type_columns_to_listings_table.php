<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('listing_type')->default('FL')->after('category_id')->index();
            $table->string('promotional_type')->default('NONE')->after('listing_type')->index();
            $table->string('slug')->nullable()->unique()->after('title');
            $table->unsignedBigInteger('views_count')->default(0)->after('slug');
            $table->timestamp('expires_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['listing_type', 'promotional_type', 'slug', 'views_count', 'expires_at']);
        });
    }
};
