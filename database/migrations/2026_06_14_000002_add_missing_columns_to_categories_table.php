<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->string('icon')->nullable()->after('slug');
            $table->string('image')->nullable()->after('icon');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('image');
            $table->unsignedInteger('sort_order')->default(0)->after('commission_rate');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'icon', 'image', 'commission_rate', 'sort_order', 'is_active']);
        });
    }
};
