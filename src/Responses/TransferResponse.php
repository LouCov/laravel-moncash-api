<?php

namespace LouCov\LaravelMonCashApi\Responses;

/**
 * Response returned by TransferService::send().
 */
final class TransferResponse
{
    /**
     * @param array<string, mixed> $transfer Raw transfer object.
     * @param array<string, mixed> $raw      Raw API response body.
     */
    public function __construct(
        public readonly array $transfer,
        public readonly array $raw,
    ) {
    }

    public function transactionId(): ?string
    {
        $value = $this->transfer['transaction_id'] ?? null;

        return $value !== null ? (string) $value : null;
    }

    public function amount(): ?int
    {
        return isset($this->transfer['amount']) ? (int) $this->transfer['amount'] : null;
    }

    public function receiver(): ?string
    {
        $value = $this->transfer['receiver'] ?? null;

        return $value !== null ? (string) $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->raw + ['transfer' => $this->transfer];
    }
}
