<?php

namespace App\Console\Commands;

use App\Support\AbandonedCartTracker;
use Illuminate\Console\Command;

/**
 * Belt-and-braces wrapper around AbandonedCartTracker::sweep() for hosts
 * that do have a working cron (`* * * * * php artisan schedule:run`).
 * Not required for correctness — the admin Abandoned Carts page runs the
 * same sweep on every load, so the list is accurate even without this.
 */
class SweepAbandonedCarts extends Command
{
    protected $signature = 'cart:sweep-abandoned';

    protected $description = 'Mark cart items idle past the abandonment threshold as Abandoned';

    public function handle(): int
    {
        AbandonedCartTracker::sweep();
        $this->info('Abandoned cart sweep complete.');

        return self::SUCCESS;
    }
}
