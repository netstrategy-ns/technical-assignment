<?php

namespace App\Console\Commands;

use App\Services\ExpireHoldsService;
use Illuminate\Console\Command;

class ExpireHoldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expire-holds {--chunk=500 : Numero massimo di hold da aggiornare per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Segna come scadute le hold attive con expires_at gia superata';

    /**
     * Execute the console command.
     */
    public function handle(ExpireHoldsService $expireHoldsService): int
    {
        $expiredCount = $expireHoldsService->run((int) $this->option('chunk'));

        if ($expiredCount === 0) {
            $this->info('Nessuna hold scaduta da aggiornare.');

            return self::SUCCESS;
        }

        $this->info("Hold scadute aggiornate: {$expiredCount}.");

        return self::SUCCESS;
    }
}
