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
        foreach (['listings', 'rental_units'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('agent_validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('agent_validated_at')->nullable();
                $table->foreignId('manager_accepted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('manager_accepted_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('valid_id_file')->nullable();
                $table->string('or_cr_file')->nullable();
            });
        }

        Schema::table('dealer_profiles', function (Blueprint $table): void {
            $table->foreignId('manager_validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_validated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealer_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('manager_validated_by');
            $table->dropColumn('manager_validated_at');
            $table->dropConstrainedForeignId('approved_by');
        });

        foreach (['listings', 'rental_units'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('agent_validated_by');
                $table->dropColumn('agent_validated_at');
                $table->dropConstrainedForeignId('manager_accepted_by');
                $table->dropColumn('manager_accepted_at');
                $table->dropConstrainedForeignId('approved_by');
                $table->dropColumn(['valid_id_file', 'or_cr_file']);
            });
        }
    }
};
