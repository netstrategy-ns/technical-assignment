<?php

namespace App\Console\Commands;

use App\Services\EventQueueService;
use Illuminate\Console\Command;

class ExpireQueueEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:expire-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire allowed queue entries past allowed_until';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expired = app(EventQueueService::class)->expireAllowed();

        $this->info("Expired entries: {$expired}");

        return Command::SUCCESS;
    }
}
