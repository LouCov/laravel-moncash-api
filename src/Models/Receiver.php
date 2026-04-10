<?php

namespace LouCov\LaravelMonCashApi\Models;

use InvalidArgumentException;

/**
 * Immutable Receiver DTO sent to the Transfert endpoint.
 */
final class Receiver
{
    public function __construct(
        public readonly int $amount,
        public readonly string $receiver,
        public readonly string $desc,
    ) {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Transfer amount must be a positive integer.');
        }

        if (trim($receiver) === '') {
            throw new InvalidArgumentException('Receiver phone number cannot be empty.');
        }
    }

    /**
     * @return array{amount: int, receiver: string, desc: string}
     */
    public function toArray(): array
    {
        return [
            'amount'   => $this->amount,
            'receiver' => $this->receiver,
            'desc'     => $this->desc,
        ];
    }
}
