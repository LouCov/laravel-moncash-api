<?php

namespace LouCov\LaravelMonCashApi\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use LouCov\LaravelMonCashApi\Authentication;
use LouCov\LaravelMonCashApi\Exceptions\MoncashConnectionException;
use LouCov\LaravelMonCashApi\Exceptions\MoncashRequestException;
use LouCov\LaravelMonCashApi\Support\Config;
use Throwable;

/**
 * Thin HTTP client that transparently authenticates requests against the
 * MonCash gateway and retries once on `401` responses (in case the cached
 * token has been invalidated server-side).
 */
class MoncashClient
{
    public function __construct(
        private readonly Config $config,
        private readonly HttpFactory $http,
        private readonly Authentication $auth,
    ) {
    }

    /**
     * POST JSON to an API path (from the `paths` config key) and return the
     * decoded response body. Throws on any non-success status.
     *
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function post(string $pathKey, array $payload): array
    {
        $url = $this->config->apiBaseUrl() . $this->config->path($pathKey);

        $response = $this->send($url, $payload);

        // Transparent retry on an expired/revoked token.
        if ($response->status() === 401) {
            $this->auth->forgetToken();
            $response = $this->send($url, $payload);
        }

        if (!$response->successful() && $response->status() !== 202) {
            throw new MoncashRequestException(
                "MonCash request to `$pathKey` failed with HTTP status {$response->status()}.",
                code: $response->status(),
                context: (array) $response->json(),
            );
        }

        return (array) $response->json();
    }

    /**
     * Build an absolute redirect URL for the hosted payment page.
     */
    public function redirectUrl(string $token): string
    {
        return $this->config->redirectBaseUrl() . $this->config->path('redirect') . $token;
    }

    /**
     * @param  array<string, mixed> $payload
     */
    private function send(string $url, array $payload): Response
    {
        try {
            return $this->http
                ->withToken($this->auth->getAccessToken())
                ->acceptJson()
                ->asJson()
                ->timeout($this->config->httpTimeout())
                ->retry(
                    $this->config->httpRetries(),
                    $this->config->httpRetryWait(),
                    throw: false,
                )
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new MoncashConnectionException(
                "Unable to reach MonCash endpoint `$url`: " . $e->getMessage(),
                previous: $e,
            );
        } catch (Throwable $e) {
            throw new MoncashRequestException(
                "Unexpected error while calling MonCash endpoint `$url`: " . $e->getMessage(),
                previous: $e,
            );
        }
    }
}
