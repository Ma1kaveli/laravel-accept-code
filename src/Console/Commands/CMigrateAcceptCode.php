<?php

namespace AcceptCode\Console\Commands;

use Illuminate\Console\Command;

class CMigrateAcceptCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:accept-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run package migrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('migrate', [
            '--path' => 'vendor/makaveli/laravel-accept-code/src/Database/migrations',
            '--force' => true
        ]);
    }
}