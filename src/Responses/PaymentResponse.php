<?php

namespace LouCov\LaravelMonCashApi\Responses;

use LouCov\LaravelMonCashApi\Models\PaymentToken;

/**
 * Response returned by PaymentService::create().
 *
 * `redirectUrl` is the absolute URL you should redirect the user to, so they
 * can complete the payment on the MonCash hosted page.
 */
final class PaymentResponse
{
    /**
     * @param array<string, mixed> $raw Raw API response body.
     */
    public function __construct(
        public readonly string $redirectUrl,
        public readonly PaymentToken $paymentToken,
        public readonly string $mode,
        public readonly string $path,
        public readonly int $timestamp,
        public readonly array $raw,
    ) {
    }

    /**
     * Shortcut to the raw token string.
     */
    public function token(): string
    {
        return $this->paymentToken->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'mode'          => $this->mode,
            'path'          => $this->path,
            'payment_token' => $this->paymentToken->toArray(),
            'timestamp'     => $this->timestamp,
            'redirect'      => $this->redirectUrl,
        ];
    }
}
