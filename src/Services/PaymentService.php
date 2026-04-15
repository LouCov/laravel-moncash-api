<?php

namespace LouCov\LaravelMonCashApi\Services;

use LouCov\LaravelMonCashApi\Exceptions\MoncashRequestException;
use LouCov\LaravelMonCashApi\Http\MoncashClient;
use LouCov\LaravelMonCashApi\Models\Order;
use LouCov\LaravelMonCashApi\Models\PaymentToken;
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

        $rawToken = (array) ($data['payment_token'] ?? []);
        $tokenString = (string) ($rawToken['token'] ?? '');

        if ($tokenString === '') {
            throw new MoncashRequestException(
                'MonCash CreatePayment response did not contain a payment token.',
                context: $data,
            );
        }

        $paymentToken = PaymentToken::fromArray($rawToken);

        return new PaymentResponse(
            redirectUrl: $this->client->redirectUrl($tokenString),
            paymentToken: $paymentToken,
            mode: (string) ($data['mode'] ?? ''),
            path: (string) ($data['path'] ?? ''),
            timestamp: (int) ($data['timestamp'] ?? 0),
            raw: $data,
        );
    }
}
