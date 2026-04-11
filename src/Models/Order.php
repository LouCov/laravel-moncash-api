<?php

namespace LouCov\LaravelMonCashApi\Models;

use InvalidArgumentException;

/**
 * Immutable payment Order DTO sent to the CreatePayment endpoint.
 */
final class Order
{
    public function __construct(
        public readonly int $amount,
        public readonly string $orderId,
    ) {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Order amount must be a positive integer.');
        }

        if (trim($orderId) === '') {
            throw new InvalidArgumentException('Order id cannot be empty.');
        }
    }

    /**
     * @return array{amount: int, orderId: string}
     */
    public function toArray(): array
    {
        return [
            'amount'  => $this->amount,
            'orderId' => $this->orderId,
        ];
    }
}
