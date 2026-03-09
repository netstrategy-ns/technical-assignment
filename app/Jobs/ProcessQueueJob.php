<?php

namespace App\Jobs;

use App\Services\QueueService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ProcessQueueJob implements ShouldQueue
{
    use Queueable;

    public function handle(QueueService $queueService): void
    {
        $queueService->processQueue();
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping('process-queue-job'),
        ];
    }
}
