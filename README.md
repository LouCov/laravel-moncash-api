
<p align="center">
    <img src="https://raw.githubusercontent.com/LouCov/laravel-moncash-api/main/.github/assets/laravel-moncash-api-logo.png" alt="loucov"/>
</p>


# Laravel Moncash API

This is the Laravel Moncash API that allows php's developers to interract with the MonCash payment facility on their website. 


## Install

Via Composer

``` bash
composer require loucov/laravel-moncash-api
```


## Publish moncash File config

``` bash
php artisan vendor:publish --tag=moncash-config
```


## Update .env file

``` bash

MONCASH_DEBUG_MODE = true //true debug mode and false live mode
MONCASH_CLIENT_ID =       //client id
MONCASH_SECRET_KEY =      //secret key
MONCASH_BUSINESS_KEY =    //business key (optional)
```


## Usage
 
``` bash
$moncash = new MoncashApi();
```

## Methode List

# For payment

```
$reponse = 
```


``` bash
$amount = 1000; // Amount
$orderId = "123456789"; // Your orderId

$response = $moncash->payment($amount, $orderId); //return object
```

If success, response object data:

```
"mode": "sandbox"
"path": "/Api/v1/CreatePayment"
"payment_token": ....
"timestamp": 1709736158019
"status": 202
"redirect": ... // Redirect link
```

If error

```

```



## Security

If you discover any security related issues, please email covilloocko@gmail.com instead of using the issue tracker.


## Credits

- [Louco COVIL][ http://www.linkedin.com/in/loucov]


## License

To be filled by LouCov