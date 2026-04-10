<?php

namespace LouCov\LaravelMonCashApi\Support;

use Throwable;

/**
 * Idempotent, atomic synchronizer for the host application's .env / .env.example
 * files. Appends any MonCash environment variables that are not yet declared.
 *
 * Safety guarantees:
 *   - never overwrites an existing value
 *   - never modifies lines unrelated to the MonCash package
 *   - uses a temp-file + rename for atomic writes
 *   - uses an exclusive advisory lock to avoid races during concurrent
 *     `package:discover` / `moncash:install` runs
 *   - silently no-ops when a target file is missing or read-only
 */
final class EnvFileSynchronizer
{
    /**
     * Curated list of env variables managed by the package.
     *
     * @var list<array{key: string, default: string, comment: string, required: bool}>
     */
    private const VARIABLES = [
        [
            'key'      => 'MONCASH_SANDBOX',
            'default'  => 'true',
            'comment'  => 'MonCash sandbox mode (true) or live mode (false).',
            'required' => true,
        ],
        [
            'key'      => 'MONCASH_CLIENT_ID',
            'default'  => '',
            'comment'  => 'MonCash OAuth client id.',
            'required' => true,
        ],
        [
            'key'      => 'MONCASH_SECRET_KEY',
            'default'  => '',
            'comment'  => 'MonCash OAuth client secret.',
            'required' => true,
        ],
        [
            'key'      => 'MONCASH_BUSINESS_KEY',
            'default'  => '',
            'comment'  => 'MonCash business key (optional).',
            'required' => false,
        ],
        [
            'key'      => 'MONCASH_HTTP_TIMEOUT',
            'default'  => '15',
            'comment'  => 'HTTP request timeout in seconds.',
            'required' => false,
        ],
        [
            'key'      => 'MONCASH_HTTP_RETRIES',
            'default'  => '2',
            'comment'  => 'Number of retries on transient HTTP failures.',
            'required' => false,
        ],
        [
            'key'      => 'MONCASH_HTTP_RETRY_WAIT',
            'default'  => '200',
            'comment'  => 'Wait between retries in milliseconds.',
            'required' => false,
        ],
        [
            'key'      => 'MONCASH_CACHE_STORE',
            'default'  => '',
            'comment'  => 'Cache store used for the OAuth access token (default store when empty).',
            'required' => false,
        ],
    ];

    private const SECTION_HEADER = '# --- MonCash API (loucov/laravel-moncash-api) ---';
    private const SECTION_FOOTER = '# --- End MonCash API ---';

    /** @var list<string> */
    private const TARGET_FILES = ['.env', '.env.example'];

    public function __construct(private readonly string $basePath)
    {
    }

    /**
     * Synchronize the env files. Returns, per file, the list of keys that
     * were freshly added.
     *
     * @return array<string, list<string>>
     */
    public function sync(): array
    {
        $report = [];

        foreach (self::TARGET_FILES as $file) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $file;
            $added = $this->syncFile($path);
            if ($added !== []) {
                $report[$file] = $added;
            }
        }

        return $report;
    }

    /**
     * @return list<array{key: string, required: bool}>
     */
    public function requiredVariables(): array
    {
        return array_values(array_map(
            fn (array $v) => ['key' => $v['key'], 'required' => $v['required']],
            array_filter(self::VARIABLES, fn (array $v) => $v['required']),
        ));
    }

    /**
     * @return list<string> Keys added to this file.
     */
    private function syncFile(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        // Open in read+write and take an exclusive advisory lock so that
        // parallel `package:discover` runs (CI, etc.) don't race.
        $handle = @fopen($path, 'r+');
        if ($handle === false) {
            return [];
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return [];
            }

            $content = stream_get_contents($handle);
            if ($content === false) {
                return [];
            }

            $missing = $this->missingVariables($content);
            if ($missing === []) {
                return [];
            }

            $newContent = $this->appendBlock($content, $missing);

            if (!$this->writeAtomically($path, $newContent)) {
                return [];
            }

            return array_map(fn (array $v) => $v['key'], $missing);
        } catch (Throwable) {
            // Never break `package:discover` on a file-write error — the
            // user can always fall back to `php artisan moncash:install`.
            return [];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * @return list<array{key: string, default: string, comment: string, required: bool}>
     */
    private function missingVariables(string $content): array
    {
        $missing = [];

        foreach (self::VARIABLES as $variable) {
            $pattern = '/^[ \t]*' . preg_quote($variable['key'], '/') . '[ \t]*=/m';
            if (!preg_match($pattern, $content)) {
                $missing[] = $variable;
            }
        }

        return $missing;
    }

    /**
     * @param list<array{key: string, default: string, comment: string, required: bool}> $variables
     */
    private function appendBlock(string $content, array $variables): string
    {
        $lines = [self::SECTION_HEADER];

        foreach ($variables as $variable) {
            $required = $variable['required'] ? ' [required]' : '';
            $lines[]  = '# ' . $variable['comment'] . $required;
            $lines[]  = $this->formatAssignment($variable['key'], $variable['default']);
        }

        $lines[] = self::SECTION_FOOTER;

        $block = implode(PHP_EOL, $lines);

        // Ensure exactly one blank line between existing content and our
        // appended block, and a trailing newline at EOF.
        $trimmed = rtrim($content, "\r\n");
        $prefix  = $trimmed === '' ? '' : $trimmed . PHP_EOL . PHP_EOL;

        return $prefix . $block . PHP_EOL;
    }

    private function formatAssignment(string $key, string $value): string
    {
        // Quote the value if it contains a space, hash, or quote so dotenv
        // parses it correctly.
        if ($value === '') {
            return $key . '=';
        }

        if (preg_match('/[\s#"\']/', $value) === 1) {
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
            return $key . '="' . $escaped . '"';
        }

        return $key . '=' . $value;
    }

    private function writeAtomically(string $path, string $content): bool
    {
        $directory = dirname($path);
        if (!is_writable($directory)) {
            return false;
        }

        try {
            $temp = tempnam($directory, '.moncash-env-');
        } catch (Throwable) {
            return false;
        }

        if ($temp === false) {
            return false;
        }

        if (@file_put_contents($temp, $content, LOCK_EX) === false) {
            @unlink($temp);
            return false;
        }

        // Preserve the original file mode so we don't accidentally widen
        // permissions on .env (it's often 0600).
        $perms = @fileperms($path);
        if ($perms !== false) {
            @chmod($temp, $perms & 0777);
        }

        if (!@rename($temp, $path)) {
            @unlink($temp);
            return false;
        }

        return true;
    }
}
