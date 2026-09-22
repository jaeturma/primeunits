<?php

namespace App\Console\Commands;

use App\Services\MembershipAccessService;
use Illuminate\Console\Command;

/**
 * Downgrades MembershipAccess.buyer_access_status when the underlying
 * Subscription has actually lapsed (past ends_at and any grace period),
 * so an expired Silver/Gold membership stops granting premium access
 * instead of persisting indefinitely — see
 * MembershipAccessService::syncExpiredMemberships().
 */
class SyncMembershipAccessExpiry extends Command
{
    protected $signature = 'membership:sync-access-expiry';

    protected $description = 'Move lapsed membership subscriptions to grace period or expired, and downgrade buyer access when grace has also passed';

    public function handle(MembershipAccessService $access): int
    {
        $counts = $access->syncExpiredMemberships();

        $this->info("Moved {$counts['grace']} subscription(s) into grace period and expired {$counts['expired']} subscription(s), downgrading buyer access where it still matched the lapsed plan.");

        return self::SUCCESS;
    }
}
