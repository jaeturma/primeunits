<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financing_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->nullOnDelete()->constrained();
            $table->foreignId('financing_partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financing_product_id')->constrained()->cascadeOnDelete();
            $table->string('reference_code')->unique();
            $table->string('full_name');
            $table->string('contact_number');
            $table->string('employment_type');
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('requested_amount', 12, 2);
            $table->decimal('down_payment', 12, 2)->nullable();
            $table->unsignedInteger('preferred_term_months');
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamp('submitted_at')->nullable()->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['financing_partner_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financing_applications');
    }
};
