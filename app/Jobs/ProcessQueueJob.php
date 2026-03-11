<?php

namespace App\Jobs;

use App\Services\QueueService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ProcessQueueJob implements ShouldQueue
{
    use Queueable;

    // Processa lo stato delle code eventi (scadenze, promozioni e pulizia)
    public function handle(QueueService $queueService): void
    {
        $queueService->processQueue();
    }

    // Evita esecuzioni concorrenti contemporanee dello stesso job
    public function middleware(): array
    {
        return [
            new WithoutOverlapping('process-queue-job'),
        ];
    }
}
