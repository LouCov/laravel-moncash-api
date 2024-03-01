<?php

namespace LouCov\LaravelMonCashApi;

/**
 * Constants
 */
class Constants {

    public static bool $DEBUG_MODE = false;

    public static string $USERNAME = ""; // client

    public static string $PASSWORD = ""; // secret

    public static string $BUSSNESS_KEY = "";

    public static string $SANDBOX_STRING = "sandbox";

    public static string $LIVE_STRING = "live";

    public static string $BASE_ENDPOINT = "";

    public static string $REDIRECT_ENDPOINT = "";

    public static string $AUTH_URI = "/oauth/token";

    public static string $PAYMENT_CREATOR_URI = "/v1/CreatePayment";

    public static string $PAYMENT_TRANSACTION_URI = "/v1/RetrieveTransactionPayment";

    public static string $PAYMENT_ORDER_URI = "/v1/RetrieveOrderPayment";

}
