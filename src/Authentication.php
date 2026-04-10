<?php

namespace LouCov\LaravelMonCashApi;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use LouCov\LaravelMonCashApi\Exceptions\AuthenticationException;
use LouCov\LaravelMonCashApi\Exceptions\MoncashConnectionException;
use LouCov\LaravelMonCashApi\Support\Config;
use Throwable;

/**
 * Handles the OAuth client-credentials flow against the MonCash gateway and
 * caches the resulting access token so that subsequent calls reuse it until
 * it expires.
 */
class Authentication
{
    public function __construct(
        private readonly Config $config,
        private readonly HttpFactory $http,
        private readonly CacheRepository $cache,
    ) {
    }

    /**
     * Return a valid OAuth access token, fetching a new one only when the
     * cached one has expired (or was never fetched).
     */
    public function getAccessToken(bool $forceRefresh = false): string
    {
        $cacheKey = $this->config->cacheKey();

        if (!$forceRefresh) {
            $cached = $this->cache->get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        [$token, $ttl] = $this->requestNewToken();

        $this->cache->put($cacheKey, $token, max(1, $ttl));

        return $token;
    }

    /**
     * Forget any cached access token. Useful on `401` responses so that the
     * next call re-authenticates.
     */
    public function forgetToken(): void
    {
        $this->cache->forget($this->config->cacheKey());
    }

    /**
     * @return array{0: string, 1: int} [token, ttlSeconds]
     */
    private function requestNewToken(): array
    {
        $endpoint = $this->config->apiBaseUrl() . $this->config->path('oauth');

        try {
            $response = $this->http
                ->withBasicAuth($this->config->clientId(), $this->config->secretKey())
                ->acceptJson()
                ->asForm()
                ->timeout($this->config->httpTimeout())
                ->retry(
                    $this->config->httpRetries(),
                    $this->config->httpRetryWait(),
                    throw: false,
                )
                ->post($endpoint, [
                    'scope'      => 'read,write',
                    'grant_type' => 'client_credentials',
                ]);
        } catch (ConnectionException $e) {
            throw new MoncashConnectionException(
                'Unable to reach MonCash authentication endpoint: ' . $e->getMessage(),
                previous: $e,
            );
        } catch (Throwable $e) {
            throw new AuthenticationException(
                'Unexpected error while authenticating with MonCash: ' . $e->getMessage(),
                previous: $e,
            );
        }

        if (!$response->successful()) {
            throw new AuthenticationException(
                'MonCash authentication failed with HTTP status ' . $response->status(),
                code: $response->status(),
                context: (array) $response->json(),
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = (array) $response->json();

        $token = $payload['access_token'] ?? null;
        if (!is_string($token) || $token === '') {
            throw new AuthenticationException(
                'MonCash authentication response did not contain an access_token.',
                context: $payload,
            );
        }

        $expiresIn = (int) ($payload['expires_in'] ?? 60);
        $ttl = max(1, $expiresIn - $this->config->cacheTtlBuffer());

        return [$token, $ttl];
    }
}
