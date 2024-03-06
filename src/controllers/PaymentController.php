<?php

namespace LouCov\LaravelMonCashApi\Controllers;
use LouCov\LaravelMonCashApi\Models\Order;
use LouCov\LaravelMonCashApi\Helpers\Helpers;

/**
 * PaymentController
 */
class PaymentController {

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
     * paymentToken
     *
     * @param  mixed $data
     * @return object
     */
    private function paymentToken(array $data) : object {

        $data = (object) $data;

        return (object) $data->payment_token;
    }

    /**
     * getPayment
     *
     * @param  array $data
     * @return object
     */
    private function getPayment(array $data) : object {

        $tokenData = $this->paymentToken($data);
        $redirectUri = $this->constants->redirect_uri.$tokenData->token;

        $redirect = Helpers::fullUrl(
            $this->constants->redirect_endpoint,
            $redirectUri
        );

        return (object) [
            ...$data,
            "payment_token" => $tokenData,
            "redirect" => $redirect
        ];
    }

    /**
     * payment
     *
     * @param  Order $order
     * @return object
     */
    public function payment(Order $order) : object {

        $endpoint = Helpers::fullUrl($this->constants->base_endpoint, $this->constants->create_payment_uri);

        $response = Helpers::requestWithToken($endpoint, $order->toArray());

        if (isset($response->status)) {
            // Auth error
            return $response;
        }

        if ($response->accepted()){
            // Status 202
            return $this->getPayment($response->json());
        }

        // Payment request error
        return (object) $response->json();
    }
}
