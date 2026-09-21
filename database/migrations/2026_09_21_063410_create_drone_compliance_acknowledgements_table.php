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
        Schema::create('drone_compliance_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('drone_compliance_notice_id')->nullable();
            $table->foreign('drone_compliance_notice_id', 'drone_compliance_ack_notice_fk')
                ->references('id')->on('drone_compliance_notices')->nullOnDelete();
            $table->string('notice_version');
            $table->text('notice_text');
            $table->timestamp('acknowledged_at');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['rental_booking_id', 'user_id'], 'drone_compliance_ack_booking_user_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drone_compliance_acknowledgements');
    }
};
