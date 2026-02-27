<?php

namespace App\Console\Commands;

use App\Services\HoldService;
use Illuminate\Console\Command;

class ExpireHoldsCommand extends Command
{
    protected $signature = 'holds:expire';

    protected $description = 'Mark expired holds as expired and release tickets';

    public function handle(HoldService $holdService): int
    {
        $count = $holdService->expireHolds();

        if ($count > 0) {
            $this->info("Expired {$count} hold(s).");
        }

        return self::SUCCESS;
    }
}
