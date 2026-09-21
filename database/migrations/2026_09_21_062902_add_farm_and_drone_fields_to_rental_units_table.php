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
        Schema::table('rental_units', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->decimal('price_per_hectare', 10, 2)->nullable()->after('price_per_hour');
            $table->decimal('operator_fee', 10, 2)->nullable()->after('price_per_hectare');
            $table->decimal('transportation_fee', 10, 2)->nullable()->after('operator_fee');
            $table->decimal('security_deposit', 10, 2)->nullable()->after('transportation_fee');
            $table->boolean('fuel_included')->nullable()->after('security_deposit');
            $table->boolean('operator_included')->default(false)->after('fuel_included');
            $table->boolean('transportation_included')->default(false)->after('operator_included');
            $table->decimal('minimum_area_hectares', 8, 2)->nullable()->after('transportation_included');
            $table->string('minimum_rental_duration')->nullable()->after('minimum_area_hectares');
            $table->text('service_coverage_area')->nullable()->after('minimum_rental_duration');
            $table->boolean('requires_verified_drone_operator')->default(false)->after('service_coverage_area');
            $table->boolean('allows_self_operation')->default(true)->after('requires_verified_drone_operator');
            $table->text('intended_uses')->nullable()->after('allows_self_operation');
            $table->foreignId('drone_pilot_user_id')->nullable()->after('intended_uses')->constrained('users')->nullOnDelete();
            $table->string('compliance_status')->default('not_applicable')->after('drone_pilot_user_id');

            $table->index(['category_id', 'rental_type']);
            $table->index('compliance_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_units', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['drone_pilot_user_id']);
            $table->dropColumn([
                'category_id',
                'price_per_hectare',
                'operator_fee',
                'transportation_fee',
                'security_deposit',
                'fuel_included',
                'operator_included',
                'transportation_included',
                'minimum_area_hectares',
                'minimum_rental_duration',
                'service_coverage_area',
                'requires_verified_drone_operator',
                'allows_self_operation',
                'intended_uses',
                'drone_pilot_user_id',
                'compliance_status',
            ]);
        });
    }
};
