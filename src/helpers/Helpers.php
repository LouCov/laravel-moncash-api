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

        return Http::withToken($auth->access_token)
            ->acceptJson()
            ->contentType($const->header_content_type)
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

        if ($const->debug_mode) {
            return $const->https_string.$const->sandbox_string.".".$baseEndpoint.$uri;
        }

        return $const->https_string.$baseEndpoint.$uri;
    }

    /**
     *
     * constant
     *
     * @return object
     */
    public static function constants () : object {

        return (object) config('moncash');
    }
}
