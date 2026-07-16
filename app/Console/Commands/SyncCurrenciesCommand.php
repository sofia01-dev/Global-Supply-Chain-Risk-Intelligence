<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncCurrenciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync daily currency rates from API and record history';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\Api\CurrencyApiService $service)
    {
        $this->info('Starting currency sync...');
        $service->syncCurrencies();
        $this->info('Currency sync completed.');
    }
}
