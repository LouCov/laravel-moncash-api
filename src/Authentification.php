<?php

namespace LouCov\LaravelMonCashApi;
use Illuminate\Support\Facades\Http;
use LouCov\LaravelMonCashApi\Helpers\Helpers;
use Illuminate\Http\Client\ConnectionException;


/**
 * Authentification
 */
class Authentification {

    private  object $const;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
        $this->const = Helpers::constants();
    }

    public function getAuthentificationData() : object {

        $baseEndpoint = Helpers::fullUrl($this->const->endpoint->base, $this->const->uri->oauth);

        try {
            $response = Http::withBasicAuth( $this->const->identifier->client, $this->const->identifier->secret)
                ->acceptJson()
                ->contentType($this->const->header->content_type)
                ->asForm()
                ->post(
                    $baseEndpoint,
                    $this->const->header->oauth_params
                );

        } catch (ConnectionException $e) {
            if ($this->const->mode->app_debug) {
                dd($e->getMessage());
            }
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
