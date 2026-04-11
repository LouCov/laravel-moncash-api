<?php

namespace LouCov\LaravelMonCashApi;

use LouCov\LaravelMonCashApi\Support\ComposerJsonEditor;
use LouCov\LaravelMonCashApi\Support\EnvFileSynchronizer;
use Throwable;

/**
 * Static handlers invoked by Composer script events.
 *
 * prePackageUninstall is registered in the host application's composer.json
 * by MoncashServiceProvider during package discovery. It runs before Composer
 * removes the vendor files, so our classes are still autoloadable.
 */
final class ComposerScripts
{
    private const PACKAGE_NAME = 'loucov/laravel-moncash-api';

    /**
     * Composer fires this before removing any package.
     * We guard against other packages and run cleanup only for ours.
     */
    public static function prePackageUninstall(mixed $event): void
    {
        try {
            $packageName = $event->getOperation()->getPackage()->getName();
        } catch (Throwable) {
            return;
        }

        if ($packageName !== self::PACKAGE_NAME) {
            return;
        }

        $root = self::projectRoot($event);
        if ($root === null) {
            return;
        }

        self::cleanup($root);
    }

    // -------------------------------------------------------------------------

    /**
     * Remove everything the package added to the host application.
     * Centralised so both the Composer hook and the artisan command share
     * the same logic.
     */
    public static function cleanup(string $root): void
    {
        self::removeConfig($root);
        self::removeEnvVariables($root);
        self::removeComposerHook($root);
    }

    private static function removeConfig(string $root): void
    {
        $path = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'moncash.php';

        if (!is_file($path)) {
            return;
        }

        try {
            if (@unlink($path)) {
                self::stderr('[moncash] Removed config/moncash.php');
            }
        } catch (Throwable) {
        }
    }

    private static function removeEnvVariables(string $root): void
    {
        try {
            $sync   = new EnvFileSynchronizer($root);
            $report = $sync->unsync();

            foreach ($report as $file => $keys) {
                self::stderr(sprintf(
                    '[moncash] Removed %d variable(s) from %s: %s',
                    count($keys),
                    $file,
                    implode(', ', $keys),
                ));
            }
        } catch (Throwable) {
        }
    }

    private static function removeComposerHook(string $root): void
    {
        try {
            ComposerJsonEditor::removeUninstallHook($root);
        } catch (Throwable) {
        }
    }

    /**
     * Resolve the Laravel project root.
     *
     * Prefers the Composer-provided vendor-dir (accurate, no guessing), then
     * falls back to walking up from __DIR__ (works when called outside of a
     * Composer event context).
     */
    private static function projectRoot(mixed $event = null): ?string
    {
        // Primary: ask Composer where its vendor directory lives.
        if ($event !== null) {
            try {
                $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
                if (is_string($vendorDir) && $vendorDir !== '') {
                    return dirname($vendorDir);
                }
            } catch (Throwable) {
                // Fall through to the directory-walk approach.
            }
        }

        // Fallback: ascend from this file (vendor/loucov/laravel-moncash-api/src)
        // up to the project root, identified by the presence of `artisan`.
        $dir = __DIR__;
        for ($i = 0; $i < 6; $i++) {
            $dir = dirname($dir);
            if (is_file($dir . DIRECTORY_SEPARATOR . 'artisan')) {
                return $dir;
            }
        }

        return null;
    }

    private static function stderr(string $message): void
    {
        @fwrite(
            defined('STDERR') ? STDERR : fopen('php://stderr', 'w'),
            '  ' . $message . PHP_EOL,
        );
    }
}
