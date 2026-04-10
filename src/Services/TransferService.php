<?php

namespace LouCov\LaravelMonCashApi\Services;

use LouCov\LaravelMonCashApi\Http\MoncashClient;
use LouCov\LaravelMonCashApi\Models\Receiver;
use LouCov\LaravelMonCashApi\Responses\TransferResponse;

/**
 * Sends money to a MonCash account via the Transfert endpoint.
 */
class TransferService
{
    public function __construct(private readonly MoncashClient $client)
    {
    }

    public function send(Receiver $receiver): TransferResponse
    {
        $data = $this->client->post('transfer', $receiver->toArray());

        return new TransferResponse(
            transfer: (array) ($data['transfer'] ?? []),
            raw: $data,
        );
    }
}
