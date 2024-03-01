<?php

namespace LouCov\LaravelMonCashApi\Controllers;

use LouCov\LaravelMonCashApi\Helpers\Helpers;


/**
 * TransactionController
 */
class TransactionController {

    private object $constants;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
        $this->constants = Helpers::constants();
    }

    /**
     * paymentObject
     *
     * @param  mixed $data
     * @return object
     */
    public function paymentObject(array $data) : object {

        $data = (object) $data;

        return (object) $data->payment;
    }

    /**
     * transactionDetails
     *
     * @param  mixed $transationId
     * @return object
     */
    public function transactionDetails(mixed $transationId) : object {

        $endpoint = Helpers::fullUrl(
            $this->constants->base_endpoint,
            $this->constants->retrieve_transaction_uri
        );

        $response = Helpers::requestWithToken($endpoint, ["transactionId" => $transationId]);
        $data = $response->json();

        if ($response->ok()) {
            return (object) [
                ...$data,
                "payment" => $this->paymentObject($data)
            ];
        }

    //    redirect to error page
        return (object) $response->json();
    }

    /**
     * orderDetails
     *
     * @param  mixed $orderId
     * @return object
     */
    public function orderDetails(mixed $orderId) : object {

        $endpoint = Helpers::fullUrl(
            $this->constants->base_endpoint,
            $this->constants->retrieve_order_uri
        );

        $response = Helpers::requestWithToken($endpoint, ["orderId" => $orderId ]);
        $data = $response->json();

        if ($response->ok()) {
            return (object) [
                ...$data,
                "payment" => $this->paymentObject($data)
            ];
        }

    //    redirect to error page
        return (object) $response->json();
    }
}
