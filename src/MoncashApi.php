<?php

namespace LouCov\LaravelMonCashApi;
use LouCov\LaravelMonCashApi\Models\Order;
use LouCov\LaravelMonCashApi\Controllers\PaymentController;
use LouCov\LaravelMonCashApi\Controllers\TransferController;
use LouCov\LaravelMonCashApi\Controllers\TransactionController;
use LouCov\LaravelMonCashApi\Models\Receiver;


/**
 * MoncashApi
 */
class MoncashApi {

    public static function payment (int $amount, mixed $orderId) {

        $payment = new PaymentController();
        $order = new Order($amount, $orderId);

        return $payment->payment($order);
    }

    public static function paymentDetailsByTransationId( mixed $transactionId) {

        $transaction = new TransactionController();

        return $transaction->transactionDetails($transactionId);
    }

    public static function paymentDetailsByOrderId( mixed $orderId) {

        $transaction = new TransactionController();

        return $transaction->orderDetails($orderId);
    }

    public static function transfer(int $amount, mixed $receiver, string $desc) {

        $transfer = new TransferController();

        $receiver = new Receiver($amount, $receiver, $desc);

        return $transfer->transfer($receiver);
    }
}
