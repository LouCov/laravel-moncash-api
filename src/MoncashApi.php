<?php

namespace LouCov\LaravelMonCashApi;

use LouCov\LaravelMonCashApi\Models\Order;
use LouCov\LaravelMonCashApi\Models\Receiver;
use LouCov\LaravelMonCashApi\Responses\PaymentResponse;
use LouCov\LaravelMonCashApi\Responses\TransactionResponse;
use LouCov\LaravelMonCashApi\Responses\TransferResponse;
use LouCov\LaravelMonCashApi\Services\PaymentService;
use LouCov\LaravelMonCashApi\Services\TransactionService;
use LouCov\LaravelMonCashApi\Services\TransferService;

/**
 * Public entry point for the MonCash package.
 *
 * Prefer resolving this class from the Laravel container (constructor
 * injection) or via the `MoncashApi` facade. The class is final and
 * immutable; all state lives in the underlying services.
 *
 * @see \LouCov\LaravelMonCashApi\Facades\MoncashApi
 */
final class MoncashApi
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly TransferService $transfers,
        private readonly TransactionService $transactions,
    ) {
    }

    /**
     * Create a new payment request and return the hosted-page redirect URL.
     *
     * @throws \LouCov\LaravelMonCashApi\Exceptions\MoncashException
     */
    public function payment(int $amount, string $orderId): PaymentResponse
    {
        return $this->payments->create(new Order($amount, $orderId));
    }

    /**
     * Retrieve payment details from MonCash by transaction id.
     *
     * @throws \LouCov\LaravelMonCashApi\Exceptions\MoncashException
     */
    public function paymentDetailsByTransactionId(string $transactionId): TransactionResponse
    {
        return $this->transactions->findByTransactionId($transactionId);
    }

    /**
     * Retrieve payment details from MonCash by order id.
     *
     * @throws \LouCov\LaravelMonCashApi\Exceptions\MoncashException
     */
    public function paymentDetailsByOrderId(string $orderId): TransactionResponse
    {
        return $this->transactions->findByOrderId($orderId);
    }

    /**
     * Send money from the business wallet to a MonCash account.
     *
     * @throws \LouCov\LaravelMonCashApi\Exceptions\MoncashException
     */
    public function transfer(int $amount, string $receiver, string $desc = ''): TransferResponse
    {
        return $this->transfers->send(new Receiver($amount, $receiver, $desc));
    }

    /**
     * Back-compat alias of paymentDetailsByTransactionId().
     *
     * @deprecated Use paymentDetailsByTransactionId() instead.
     * @throws \LouCov\LaravelMonCashApi\Exceptions\MoncashException
     */
    public function paymentDetailsByTransationId(string $transactionId): TransactionResponse
    {
        return $this->paymentDetailsByTransactionId($transactionId);
    }
}
