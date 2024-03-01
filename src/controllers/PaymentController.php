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
    public function paymentToken(array $data) : object {

        $data = (object) $data;

        return (object) $data->payment_token;
    }

    /**
     * paymentRequest
     *
     * @param  mixed $order
     * @return array
     */
    public function paymentRequest(Order $order) : array {

        $endpoint = Helpers::fullUrl(
            $this->constants->base_endpoint,
            $this->constants->create_payment_uri
        );

        $response = Helpers::requestWithToken($endpoint, $order->toArray());

        if ($response->accepted()) {
            return $response->json();
        }

    //    redirect to error page
        return $response->json();
    }

    /**
     * payment
     *
     * @param  mixed $order
     * @return object
     */
    public function payment(Order $order) : object {

        $data = $this->paymentRequest($order);
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
}
