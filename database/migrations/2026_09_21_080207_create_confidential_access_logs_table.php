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
        Schema::create('confidential_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['listing_id', 'created_at'], 'confidential_access_logs_listing_created_idx');
            $table->index(['user_id', 'created_at'], 'confidential_access_logs_user_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confidential_access_logs');
    }
};
