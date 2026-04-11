<?php

namespace LouCov\LaravelMonCashApi\Facades;

use Illuminate\Support\Facades\Facade;
use LouCov\LaravelMonCashApi\MoncashApi as MoncashApiService;
use LouCov\LaravelMonCashApi\Responses\PaymentResponse;
use LouCov\LaravelMonCashApi\Responses\TransactionResponse;
use LouCov\LaravelMonCashApi\Responses\TransferResponse;

/**
 * @method static PaymentResponse     payment(int $amount, string $orderId)
 * @method static TransactionResponse paymentDetailsByTransactionId(string $transactionId)
 * @method static TransactionResponse paymentDetailsByOrderId(string $orderId)
 * @method static TransferResponse    transfer(int $amount, string $receiver, string $desc)
 *
 * @see MoncashApiService
 */
class MoncashApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'moncash';
    }
}
