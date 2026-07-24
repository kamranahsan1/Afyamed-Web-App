<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DeployCommand extends Command
{
    protected $signature = 'app:deploy';

    protected $description = 'Clear cache, run migrations, seed database and optimize application';

    public function handle(): int
    {
        $this->info('Clearing cache...');
        Artisan::call('optimize:clear');
        $this->output->write(Artisan::output());

        $this->info('Running migrations...');
        Artisan::call('migrate', [
            '--force' => true,
        ]);
        $this->output->write(Artisan::output());

        $this->info('Running seeders...');
        Artisan::call('db:seed', [
            '--force' => true,
        ]);
        $this->output->write(Artisan::output());

        $this->info('Optimizing application...');
        Artisan::call('optimize');
        $this->output->write(Artisan::output());

        $this->info('Deployment completed successfully.');

        return self::SUCCESS;
    }
}