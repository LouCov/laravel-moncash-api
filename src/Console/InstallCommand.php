<?php

namespace LouCov\LaravelMonCashApi\Console;

use Illuminate\Console\Command;
use LouCov\LaravelMonCashApi\MoncashServiceProvider;
use LouCov\LaravelMonCashApi\Support\EnvFileSynchronizer;

/**
 * Installer command that publishes the config and seeds the host
 * application's .env / .env.example files with the MonCash variables.
 *
 * Running `composer require loucov/laravel-moncash-api` already triggers
 * the same steps automatically (via `package:discover`); this command lets
 * users re-run the install or force-overwrite an existing config with --force.
 */
class InstallCommand extends Command
{
    protected $signature = 'moncash:install
                            {--force : Overwrite an existing published config file}';

    protected $description = 'Publish the MonCash config and seed the .env files with the required variables.';

    public function handle(): int
    {
        $this->components->info('Installing the MonCash API package...');

        $this->call('vendor:publish', array_filter([
            '--provider' => MoncashServiceProvider::class,
            '--tag'      => 'moncash-config',
            '--force'    => $this->option('force') ? true : null,
        ], fn ($value) => $value !== null));

        $sync   = new EnvFileSynchronizer($this->laravel->basePath());
        $report = $sync->sync();

        if ($report === []) {
            $this->components->info('No new MonCash environment variables to add — your .env files are up to date.');
        } else {
            foreach ($report as $file => $keys) {
                $this->components->info(sprintf('Added %d variable(s) to %s: %s', count($keys), $file, implode(', ', $keys)));
            }

            $required = array_column($sync->requiredVariables(), 'key');
            if ($required !== []) {
                $this->newLine();
                $this->components->warn('Remember to fill in the required values in your .env file:');
                foreach ($required as $key) {
                    $this->line('  • ' . $key);
                }
            }
        }

        return self::SUCCESS;
    }
}
