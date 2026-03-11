<?php

namespace App\Jobs;

use App\Services\ExpireHoldsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ExpireHoldsJob implements ShouldQueue
{
    use Queueable;

    // Esegue la pulizia periodica degli hold scaduti
    public function handle(ExpireHoldsService $expireHoldsService): void
    {
        $expireHoldsService->run();
    }

    // Evita esecuzioni concorrenti contemporanee dello stesso job
    public function middleware(): array
    {
        return [
            new WithoutOverlapping('expire-holds-job'),
        ];
    }
}
