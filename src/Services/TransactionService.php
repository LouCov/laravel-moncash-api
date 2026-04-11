<?php

namespace LouCov\LaravelMonCashApi\Services;

use InvalidArgumentException;
use LouCov\LaravelMonCashApi\Http\MoncashClient;
use LouCov\LaravelMonCashApi\Responses\TransactionResponse;

/**
 * Retrieves payment/transaction details from the MonCash gateway.
 */
class TransactionService
{
    public function __construct(private readonly MoncashClient $client)
    {
    }

    public function findByTransactionId(string $transactionId): TransactionResponse
    {
        if (trim($transactionId) === '') {
            throw new InvalidArgumentException('Transaction id cannot be empty.');
        }

        return $this->fetch('retrieve_transaction', ['transactionId' => $transactionId]);
    }

    public function findByOrderId(string $orderId): TransactionResponse
    {
        if (trim($orderId) === '') {
            throw new InvalidArgumentException('Order id cannot be empty.');
        }

        return $this->fetch('retrieve_order', ['orderId' => $orderId]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function fetch(string $pathKey, array $payload): TransactionResponse
    {
        $data = $this->client->post($pathKey, $payload);

        return new TransactionResponse(
            payment: (array) ($data['payment'] ?? []),
            raw: $data,
        );
    }
}
