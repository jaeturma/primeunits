<?php

namespace Database\Seeders;

use App\Models\ConfidentialityNotice;
use Illuminate\Database\Seeder;

class ConfidentialityNoticeSeeder extends Seeder
{
    public function run(): void
    {
        ConfidentialityNotice::query()->where('version', '!=', 'v1')->update(['is_active' => false]);

        ConfidentialityNotice::query()->updateOrCreate(
            ['version' => 'v1'],
            [
                'body' => ConfidentialityNotice::DEFAULT_NOTICE_TEXT,
                'is_active' => true,
                'published_at' => now()->subMonths(2),
            ],
        );
    }
}
