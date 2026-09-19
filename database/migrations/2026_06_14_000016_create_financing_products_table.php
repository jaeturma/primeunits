<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financing_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('financing_partner_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('product_type');
            $table->text('description')->nullable();
            $table->decimal('min_amount', 12, 2);
            $table->decimal('max_amount', 12, 2);
            $table->decimal('interest_rate_min', 5, 2);
            $table->decimal('interest_rate_max', 5, 2);
            $table->unsignedInteger('min_term_months')->default(6);
            $table->unsignedInteger('max_term_months')->default(60);
            $table->json('applicable_categories')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['financing_partner_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financing_products');
    }
};
