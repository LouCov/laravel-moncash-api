<?php

namespace LouCov\LaravelMonCashApi\Controllers;

use LouCov\LaravelMonCashApi\Helpers\Helpers;


/**
 * TransactionController
 */
class TransactionController {

    private object $const;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
        $this->const = Helpers::constants();
    }

    /**
     * paymentObject
     *
     * @param  mixed $data
     * @return object
     */
    private function paymentObject(array $data) : object {

        $data = (object) $data;

        return (object) $data->payment;
    }

    /**
     * getPayment
     *
     * @param  mixed $response
     * @return object
     */
    private function getPayment(mixed $response) : object {

        if (isset($response->status)) {
            // Auth error
            return $response;
        }

        if ($response->ok()) {
            $data = $response->json();

            return (object) [
                ...$data,
                "payment" => $this->paymentObject($data)
            ];
        }

    //    Transaction details request error
        return (object) $response->json();
    }

    /**
     * transactionDetails
     *
     * @param  mixed $transationId
     * @return object
     */
    public function transactionDetails(mixed $transationId) : object {

        $endpoint = Helpers::fullUrl( $this->const->endpoint->base,
            $this->const->uri->retrieve_transaction
        );

        $response = Helpers::requestWithToken($endpoint, [
            $this->const->string->transaction_id => $transationId
        ]);

        return $this->getPayment($response);
    }

    /**
     * orderDetails
     *
     * @param  mixed $orderId
     * @return object
     */
    public function orderDetails(mixed $orderId) : object {

        $endpoint = Helpers::fullUrl( $this->const->endpoint->base,
            $this->const->uri->retrieve_order);

        $response = Helpers::requestWithToken($endpoint, [
            $this->const->string->order_id => $orderId 
        ]);

        return $this->getPayment($response);
    }
}
