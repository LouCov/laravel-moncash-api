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

        $endpoint = Helpers::fullUrl( $this->constants->base_endpoint,
            $this->constants->retrieve_transaction_uri
        );

        $response = Helpers::requestWithToken($endpoint, ["transactionId" => $transationId]);

        return $this->getPayment($response);
    }

    /**
     * orderDetails
     *
     * @param  mixed $orderId
     * @return object
     */
    public function orderDetails(mixed $orderId) : object {

        $endpoint = Helpers::fullUrl( $this->constants->base_endpoint,
            $this->constants->retrieve_order_uri);

        $response = Helpers::requestWithToken($endpoint, ["orderId" => $orderId ]);

        return $this->getPayment($response);
    }
}
