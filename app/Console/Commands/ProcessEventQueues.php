<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Services\EventQueueService;
use Illuminate\Console\Command;

class ProcessEventQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process event queues, allowing next users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::query()
            ->where('queue_enabled', true)
            ->get();

        $totalAllowed = 0;

        $service = app(EventQueueService::class);

        foreach ($events as $event) {
            $totalAllowed += $service->allowNext($event);
        }

        $this->info("Allowed entries: {$totalAllowed}");

        return Command::SUCCESS;
    }
}
