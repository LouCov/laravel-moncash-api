<?php

namespace LouCov\LaravelMonCashApi\Controllers;

use LouCov\LaravelMonCashApi\Helpers\Helpers;
use LouCov\LaravelMonCashApi\Models\Receiver;



/**
 * TransferController
 */
class TransferController {

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
     * transferObject
     *
     * @param  mixed $data
     * @return object
     */
    private function transferObject(array $data) : object {

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

        $endpoint = Helpers::fullUrl( $this->const->endpoint->base, $this->const->uri->transfert );
        $response = Helpers::requestWithToken($endpoint, $receiver->toArray());

        if (isset($response->status)) {
            // Auth error
            return $response;
        }

        if ($response->ok()) {
            $data = $response->json();
            return (object) [
                ...$data,
                "transfer" => $this->transferObject($data)
            ];
        }

    //    Transfer request error
        return (object) $response->json();
    }
}
