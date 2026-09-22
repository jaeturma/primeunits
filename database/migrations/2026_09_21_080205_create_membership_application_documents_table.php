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
        Schema::create('membership_application_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_application_id');
            $table->foreign('membership_application_id', 'membership_app_documents_app_fk')
                ->references('id')->on('membership_applications')->cascadeOnDelete();
            $table->string('label');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_application_documents');
    }
};
