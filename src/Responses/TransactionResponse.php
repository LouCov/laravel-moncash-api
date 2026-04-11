<?php

namespace LouCov\LaravelMonCashApi\Responses;

/**
 * Response returned by TransactionService::findByTransactionId() and
 * findByOrderId().
 */
final class TransactionResponse
{
    /**
     * @param array<string, mixed> $payment Raw payment object.
     * @param array<string, mixed> $raw     Raw API response body.
     */
    public function __construct(
        public readonly array $payment,
        public readonly array $raw,
    ) {
    }

    public function transactionId(): ?string
    {
        $value = $this->payment['transaction_id'] ?? null;

        return $value !== null ? (string) $value : null;
    }

    public function reference(): ?string
    {
        $value = $this->payment['reference'] ?? null;

        return $value !== null ? (string) $value : null;
    }

    public function cost(): ?int
    {
        return isset($this->payment['cost']) ? (int) $this->payment['cost'] : null;
    }

    public function message(): ?string
    {
        $value = $this->payment['message'] ?? null;

        return $value !== null ? (string) $value : null;
    }

    public function payer(): ?string
    {
        $value = $this->payment['payer'] ?? null;

        return $value !== null ? (string) $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->raw + ['payment' => $this->payment];
    }
}
