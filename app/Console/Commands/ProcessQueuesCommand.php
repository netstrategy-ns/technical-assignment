<?php

namespace App\Console\Commands;

use App\Services\QueueService;
use Illuminate\Console\Command;

class ProcessQueuesCommand extends Command
{
    protected $signature = 'queues:process';

    protected $description = 'Process event queues and activate next users';

    public function handle(QueueService $queueService): int
    {
        $activated = $queueService->processQueues();

        if ($activated > 0) {
            $this->info("Activated {$activated} queue entry(ies).");
        }

        return self::SUCCESS;
    }
}
