<?php

namespace LouCov\LaravelMonCashApi\Services;

use LouCov\LaravelMonCashApi\Exceptions\MoncashRequestException;
use LouCov\LaravelMonCashApi\Http\MoncashClient;
use LouCov\LaravelMonCashApi\Models\Order;
use LouCov\LaravelMonCashApi\Responses\PaymentResponse;

/**
 * Creates payment requests against the MonCash gateway.
 */
class PaymentService
{
    public function __construct(private readonly MoncashClient $client)
    {
    }

    public function create(Order $order): PaymentResponse
    {
        $data = $this->client->post('create_payment', $order->toArray());

        $paymentToken = (array) ($data['payment_token'] ?? []);
        $token = (string) ($paymentToken['token'] ?? '');

        if ($token === '') {
            throw new MoncashRequestException(
                'MonCash CreatePayment response did not contain a payment token.',
                context: $data,
            );
        }

        return new PaymentResponse(
            redirectUrl: $this->client->redirectUrl($token),
            paymentToken: $paymentToken,
            mode: (string) ($data['mode'] ?? ''),
            path: (string) ($data['path'] ?? ''),
            timestamp: (int) ($data['timestamp'] ?? 0),
            raw: $data,
        );
    }
}
