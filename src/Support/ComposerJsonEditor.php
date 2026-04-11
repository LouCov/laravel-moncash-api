<?php

namespace LouCov\LaravelMonCashApi\Support;

use Throwable;

/**
 * Reads and writes the host application's composer.json to register (or
 * remove) the pre-package-uninstall Composer script that triggers automatic
 * cleanup when the package is removed.
 */
final class ComposerJsonEditor
{
    private const SCRIPT_EVENT = 'pre-package-uninstall';
    private const SCRIPT_ENTRY = 'LouCov\\LaravelMonCashApi\\ComposerScripts::prePackageUninstall';

    /**
     * Add the uninstall hook to the host's composer.json if not already present.
     */
    public static function registerUninstallHook(string $basePath): void
    {
        self::edit($basePath, function (array $data): array {
            $existing = (array) ($data['scripts'][self::SCRIPT_EVENT] ?? []);

            if (in_array(self::SCRIPT_ENTRY, $existing, true)) {
                return $data; // already registered — no-op
            }

            $existing[] = self::SCRIPT_ENTRY;
            $data['scripts'][self::SCRIPT_EVENT] = $existing;

            return $data;
        });
    }

    /**
     * Remove the uninstall hook from the host's composer.json.
     * Called by ComposerScripts during the actual pre-package-uninstall run.
     */
    public static function removeUninstallHook(string $basePath): void
    {
        self::edit($basePath, function (array $data): array {
            $existing = (array) ($data['scripts'][self::SCRIPT_EVENT] ?? []);
            $filtered = array_values(array_filter(
                $existing,
                fn (string $entry) => $entry !== self::SCRIPT_ENTRY,
            ));

            if (count($filtered) === count($existing)) {
                return $data; // nothing to remove — no-op
            }

            if ($filtered === []) {
                unset($data['scripts'][self::SCRIPT_EVENT]);
                if (isset($data['scripts']) && $data['scripts'] === []) {
                    unset($data['scripts']);
                }
            } else {
                $data['scripts'][self::SCRIPT_EVENT] = $filtered;
            }

            return $data;
        });
    }

    /**
     * Read → transform → write the host's composer.json atomically.
     *
     * @param callable(array<string,mixed>): array<string,mixed> $transform
     */
    private static function edit(string $basePath, callable $transform): void
    {
        $path = $basePath . DIRECTORY_SEPARATOR . 'composer.json';

        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        try {
            $raw = file_get_contents($path);
            if ($raw === false) {
                return;
            }

            /** @var array<string, mixed> $data */
            $data    = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            $updated = $transform($data);

            if ($updated === $data) {
                return; // nothing changed
            }

            $json = json_encode(
                $updated,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            );

            $directory = dirname($path);
            $temp      = tempnam($directory, '.composer-moncash-');
            if ($temp === false) {
                return;
            }

            if (@file_put_contents($temp, $json . PHP_EOL, LOCK_EX) === false) {
                @unlink($temp);
                return;
            }

            // Preserve original permissions.
            $perms = @fileperms($path);
            if ($perms !== false) {
                @chmod($temp, $perms & 0777);
            }

            if (!@rename($temp, $path)) {
                @unlink($temp);
            }
        } catch (Throwable) {
            // Never crash the caller — best-effort only.
        }
    }
}
