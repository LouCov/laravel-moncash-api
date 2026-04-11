<?php

namespace LouCov\LaravelMonCashApi;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;
use LouCov\LaravelMonCashApi\Console\InstallCommand;
use LouCov\LaravelMonCashApi\Http\MoncashClient;
use LouCov\LaravelMonCashApi\Services\PaymentService;
use LouCov\LaravelMonCashApi\Services\TransactionService;
use LouCov\LaravelMonCashApi\Services\TransferService;
use LouCov\LaravelMonCashApi\Support\Config;
use LouCov\LaravelMonCashApi\Support\EnvFileSynchronizer;
use Throwable;

class MoncashServiceProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__ . '/../config/moncash.php';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'moncash');

        $this->app->singleton(Config::class, function ($app) {
            return new Config((array) $app['config']->get('moncash', []));
        });

        $this->app->singleton(Authentication::class, function ($app) {
            /** @var Config $config */
            $config = $app->make(Config::class);
            /** @var CacheFactory $cacheFactory */
            $cacheFactory = $app->make(CacheFactory::class);

            return new Authentication(
                $config,
                $app->make(HttpFactory::class),
                $cacheFactory->store($config->cacheStore()),
            );
        });

        $this->app->singleton(MoncashClient::class, function ($app) {
            return new MoncashClient(
                $app->make(Config::class),
                $app->make(HttpFactory::class),
                $app->make(Authentication::class),
            );
        });

        $this->app->singleton(PaymentService::class, fn ($app) =>
            new PaymentService($app->make(MoncashClient::class)));

        $this->app->singleton(TransferService::class, fn ($app) =>
            new TransferService($app->make(MoncashClient::class)));

        $this->app->singleton(TransactionService::class, fn ($app) =>
            new TransactionService($app->make(MoncashClient::class)));

        $this->app->singleton(MoncashApi::class, fn ($app) => new MoncashApi(
            $app->make(PaymentService::class),
            $app->make(TransferService::class),
            $app->make(TransactionService::class),
        ));

        // Short alias used by the facade.
        $this->app->alias(MoncashApi::class, 'moncash');
    }

    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            self::CONFIG_PATH => config_path('moncash.php'),
        ], 'moncash-config');

        $this->commands([
            InstallCommand::class,
        ]);

        // After every `composer install` / `composer update` / `composer
        // dump-autoload`, Laravel runs `php artisan package:discover`.
        // We piggy-back on that to seed the host application's .env file
        // with the MonCash variables — idempotently and atomically.
        if ($this->isPackageDiscoveryRun()) {
            $this->syncEnvironmentFile();
        }
    }

    /**
     * Detect whether the current Artisan invocation is `package:discover`,
     * which is the only command we want the auto-sync to react to.
     */
    private function isPackageDiscoveryRun(): bool
    {
        $argv = $_SERVER['argv'] ?? null;
        if (!\is_array($argv)) {
            return false;
        }

        foreach ($argv as $arg) {
            if ($arg === 'package:discover') {
                return true;
            }
        }

        return false;
    }

    private function syncEnvironmentFile(): void
    {
        try {
            $sync   = new EnvFileSynchronizer($this->app->basePath());
            $report = $sync->sync();
        } catch (Throwable) {
            // Never break `package:discover` on a file-write error — the
            // user can always fall back to `php artisan moncash:install`.
            return;
        }

        if ($report === []) {
            return;
        }

        // Emit a single concise line in composer's output so the user
        // notices the new variables without cluttering CI logs.
        $summary = [];
        foreach ($report as $file => $keys) {
            $summary[] = $file . ' (' . implode(', ', $keys) . ')';
        }

        @fwrite(
            \defined('STDERR') ? STDERR : fopen('php://stderr', 'w'),
            '  [moncash] Seeded environment variables into ' . implode('; ', $summary) . PHP_EOL
        );
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Config::class,
            Authentication::class,
            MoncashClient::class,
            PaymentService::class,
            TransferService::class,
            TransactionService::class,
            MoncashApi::class,
            'moncash',
        ];
    }
}
