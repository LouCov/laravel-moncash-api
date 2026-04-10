<?php

namespace LouCov\LaravelMonCashApi\Support;

use LouCov\LaravelMonCashApi\Exceptions\MoncashException;

/**
 * Typed access to the package configuration.
 *
 * Wraps the `config/moncash.php` array so callers don't have to deal with
 * bare array keys or repeatedly call `config()`.
 */
final class Config
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function isSandbox(): bool
    {
        return (bool) ($this->config['sandbox'] ?? true);
    }

    public function clientId(): string
    {
        return $this->requireString('credentials.client_id', 'MONCASH_CLIENT_ID');
    }

    public function secretKey(): string
    {
        return $this->requireString('credentials.secret_key', 'MONCASH_SECRET_KEY');
    }

    public function businessKey(): ?string
    {
        $value = data_get($this->config, 'credentials.business_key');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function apiBaseUrl(): string
    {
        $env = $this->isSandbox() ? 'sandbox' : 'live';

        return rtrim($this->requireString("endpoints.$env.api"), '/');
    }

    public function redirectBaseUrl(): string
    {
        $env = $this->isSandbox() ? 'sandbox' : 'live';

        return rtrim($this->requireString("endpoints.$env.redirect"), '/');
    }

    public function path(string $name): string
    {
        return $this->requireString("paths.$name");
    }

    public function httpTimeout(): int
    {
        return (int) ($this->config['http']['timeout'] ?? 15);
    }

    public function httpRetries(): int
    {
        return (int) ($this->config['http']['retries'] ?? 2);
    }

    public function httpRetryWait(): int
    {
        return (int) ($this->config['http']['retry_wait'] ?? 200);
    }

    public function cacheStore(): ?string
    {
        $store = $this->config['cache']['store'] ?? null;

        return is_string($store) && $store !== '' ? $store : null;
    }

    public function cacheKey(): string
    {
        return (string) ($this->config['cache']['key'] ?? 'moncash.access_token');
    }

    public function cacheTtlBuffer(): int
    {
        return (int) ($this->config['cache']['ttl_buffer'] ?? 30);
    }

    private function requireString(string $key, ?string $envHint = null): string
    {
        $value = data_get($this->config, $key);

        if (!is_string($value) || $value === '') {
            $hint = $envHint ? " (check the $envHint environment variable)" : '';
            throw new MoncashException("Missing MonCash config value for `$key`.$hint");
        }

        return $value;
    }
}
