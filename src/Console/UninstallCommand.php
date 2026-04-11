<?php

namespace LouCov\LaravelMonCashApi\Console;

use Illuminate\Console\Command;
use LouCov\LaravelMonCashApi\Support\ComposerJsonEditor;
use LouCov\LaravelMonCashApi\Support\EnvFileSynchronizer;

/**
 * Manual uninstall command.
 *
 * Run this before `composer remove loucov/laravel-moncash-api` if you want
 * to trigger cleanup explicitly. Under normal circumstances the cleanup runs
 * automatically via the pre-package-uninstall Composer hook registered by
 * MoncashServiceProvider during package discovery.
 */
class UninstallCommand extends Command
{
    protected $signature = 'moncash:uninstall';

    protected $description = 'Remove the published MonCash config and environment variables, and deregister the Composer uninstall hook.';

    public function handle(): int
    {
        $this->components->info('Uninstalling the MonCash API package...');

        // 1. Remove published config.
        $config = config_path('moncash.php');
        if (file_exists($config)) {
            unlink($config);
            $this->components->info('Removed config/moncash.php');
        } else {
            $this->components->warn('config/moncash.php not found — already removed or never published.');
        }

        // 2. Remove environment variables.
        $sync   = new EnvFileSynchronizer($this->laravel->basePath());
        $report = $sync->unsync();

        if ($report === []) {
            $this->components->warn('No MonCash variables found in .env files — already clean.');
        } else {
            foreach ($report as $file => $keys) {
                $this->components->info(sprintf(
                    'Removed %d variable(s) from %s: %s',
                    count($keys),
                    $file,
                    implode(', ', $keys),
                ));
            }
        }

        // 3. Remove the Composer hook so composer.json is left clean.
        ComposerJsonEditor::removeUninstallHook($this->laravel->basePath());
        $this->components->info('Removed pre-package-uninstall hook from composer.json.');

        $this->newLine();
        $this->components->info('You can now safely run: composer remove loucov/laravel-moncash-api');

        return self::SUCCESS;
    }
}
