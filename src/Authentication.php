<?php

namespace LouCov\LaravelMonCashApi;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use LouCov\LaravelMonCashApi\Exceptions\AuthenticationException;
use LouCov\LaravelMonCashApi\Exceptions\MoncashConnectionException;
use LouCov\LaravelMonCashApi\Support\Config;
use Throwable;

/**
 * Handles the OAuth client-credentials flow against the MonCash gateway.
 */
class Authentication
{
    public function __construct(
        private readonly Config $config,
        private readonly HttpFactory $http,
    ) {
    }

    /**
     * Fetch and return a fresh OAuth access token.
     */
    public function getAccessToken(): string
    {
        return $this->requestNewToken();
    }

    private function requestNewToken(): string
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

        return $token;
    }
}
