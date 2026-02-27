<?php

namespace App\Console\Commands;

use App\Models\Hold;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holds:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire active holds past their expiration time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $expired = Hold::query()
            ->where('status', 'active')
            ->where('expires_at', '<=', $now)
            ->update([
                'status' => 'expired',
                'updated_at' => $now,
            ]);

        $this->info("Expired holds: {$expired}");

        return Command::SUCCESS;
    }
}
