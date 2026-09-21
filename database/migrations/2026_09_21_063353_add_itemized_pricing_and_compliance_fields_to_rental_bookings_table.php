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
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->decimal('area_hectares', 8, 2)->nullable()->after('rental_package_id');
            $table->string('pricing_unit')->nullable()->after('area_hectares');
            $table->decimal('base_amount', 10, 2)->nullable()->after('quoted_price');
            $table->decimal('operator_fee_amount', 10, 2)->nullable()->after('base_amount');
            $table->decimal('transportation_fee_amount', 10, 2)->nullable()->after('operator_fee_amount');
            $table->decimal('fuel_amount', 10, 2)->nullable()->after('transportation_fee_amount');
            $table->decimal('deposit_amount', 10, 2)->nullable()->after('fuel_amount');
            $table->decimal('platform_fee_amount', 10, 2)->nullable()->after('deposit_amount');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('platform_fee_amount');
            $table->decimal('tax_amount', 10, 2)->nullable()->after('discount_amount');
            $table->decimal('total_amount', 10, 2)->nullable()->after('tax_amount');
            $table->foreignId('drone_pilot_user_id')->nullable()->after('total_amount')->constrained('users')->nullOnDelete();
            $table->json('operator_verification_snapshot')->nullable()->after('drone_pilot_user_id');
            $table->timestamp('compliance_acknowledged_at')->nullable()->after('operator_verification_snapshot');
            $table->string('compliance_notice_version')->nullable()->after('compliance_acknowledged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->dropForeign(['drone_pilot_user_id']);
            $table->dropColumn([
                'area_hectares',
                'pricing_unit',
                'base_amount',
                'operator_fee_amount',
                'transportation_fee_amount',
                'fuel_amount',
                'deposit_amount',
                'platform_fee_amount',
                'discount_amount',
                'tax_amount',
                'total_amount',
                'drone_pilot_user_id',
                'operator_verification_snapshot',
                'compliance_acknowledged_at',
                'compliance_notice_version',
            ]);
        });
    }
};
