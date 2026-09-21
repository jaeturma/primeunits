<?php

namespace Database\Seeders;

use App\Models\DroneComplianceNotice;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: the active, versioned drone compliance notice shown
 * before booking confirmation on drone rental and service listings.
 */
class DroneComplianceNoticeSeeder extends Seeder
{
    public function run(): void
    {
        DroneComplianceNotice::query()->where('version', '!=', 'v1')->update(['is_active' => false]);

        DroneComplianceNotice::query()->updateOrCreate(
            ['version' => 'v1'],
            [
                'body' => DroneComplianceNotice::DEFAULT_NOTICE_TEXT,
                'is_active' => true,
                'published_at' => now()->subMonths(3),
            ],
        );
    }
}
