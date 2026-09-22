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
        Schema::create('confidentiality_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('listing_access_request_id')->nullable();
            $table->foreign('listing_access_request_id', 'confidentiality_ack_request_fk')
                ->references('id')->on('listing_access_requests')->nullOnDelete();
            $table->unsignedBigInteger('confidentiality_notice_id')->nullable();
            $table->foreign('confidentiality_notice_id', 'confidentiality_ack_notice_fk')
                ->references('id')->on('confidentiality_notices')->nullOnDelete();
            $table->string('notice_version');
            $table->text('notice_text');
            $table->timestamp('acknowledged_at');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'listing_access_request_id'], 'confidentiality_ack_user_request_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confidentiality_acknowledgements');
    }
};
