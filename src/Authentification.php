<?php

namespace LouCov\LaravelMonCashApi;
use Illuminate\Support\Facades\Http;
use LouCov\LaravelMonCashApi\Helpers\Helpers;
use Illuminate\Http\Client\ConnectionException;


/**
 * Authentification
 */
class Authentification {

    private  object $constants;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
        $this->constants = Helpers::constants();
    }

    public function getAuthentificationData() : object {

        $baseEndpoint = Helpers::fullUrl($this->constants->base_endpoint, $this->constants->oauth_uri);

        try {
            $response = Http::withBasicAuth( $this->constants->client, $this->constants->secret)
                ->acceptJson()
                ->contentType($this->constants->header_content_type)
                ->asForm()
                ->post(
                    $baseEndpoint,
                    $this->constants->oauth_params
                );

        } catch (ConnectionException $e) {
            return (object) [
                "message" => $e->getMessage(),
            ];
        }

        if (!$response->ok()) {
            return (object) $response->json();
        }

        return (object) [
            ...$response->json(),
            "status" => $response->status()
        ];
    }
}
