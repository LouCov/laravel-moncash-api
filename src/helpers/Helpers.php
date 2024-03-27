<?php

namespace LouCov\LaravelMonCashApi\Helpers;
use Illuminate\Support\Facades\Http;
use LouCov\LaravelMonCashApi\Authentification;

/**
 * Helpers
 */
class Helpers {

    /**
     * requestWithToken
     *
     * @param  mixed $endpoint
     * @param  mixed $data
     * @return mixed
     */
    public static function requestWithToken(string $endpoint, array $data) : mixed {

        $auth = new Authentification();
        $auth = $auth->getAuthentificationData();
        $const = self::constants();

        if ($auth->status != 200) {
            return $auth;
        }

        return Http::withToken($auth->access_token)
            ->acceptJson()
            ->contentType($const->header->content_type)
            ->post($endpoint, $data);
    }

    /**
     * fullUrl
     *
     * @param  mixed $baseEndpoint
     * @param  mixed $uri
     * @param  mixed $debugMode
     * @return string
     */
    public static function fullUrl(string $baseEndpoint, string $uri) : string {

        $const = self::constants();

        if ($const->mode->debug) {
            return $const->string->https
                . $const->string->sandbox
                . "." . $baseEndpoint.$uri;
        }

        return $const->string->https . $baseEndpoint.$uri;
    }

    /**
     *
     * constant
     *
     * @return object
     */
    public static function constants () : object {

        $array = config('moncash');
        $object = json_decode(json_encode($array));

        return $object;
    }
}
