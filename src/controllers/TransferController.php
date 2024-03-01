<?php

namespace LouCov\LaravelMonCashApi\Controllers;

use LouCov\LaravelMonCashApi\Helpers\Helpers;
use LouCov\LaravelMonCashApi\Models\Receiver;



/**
 * TransferController
 */
class TransferController {

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
     * transferObject
     *
     * @param  mixed $data
     * @return object
     */
    public function transferObject(array $data) : object {

        $data = (object) $data;

        return (object) $data->transfer;
    }

    /**
     * transfert
     *
     * @param  mixed $receiver
     * @return object
     */
    public function transfer(Receiver $receiver) : object {

        $endpoint = Helpers::fullUrl(
            $this->constants->base_endpoint,
            $this->constants->transfert_uri
        );

        $response = Helpers::requestWithToken($endpoint, $receiver->toArray());
        $data = $response->json();

        if ($response->ok()) {
            return (object) [
                ...$data,
                "transfer" => $this->transferObject($data)
            ];
        }

    //    redirect to error page
        return (object) $response->json();
    }
}
