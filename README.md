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

```
// Go to the /sample directory to follow a hands on simple tutorial
```


## Security

If you discover any security related issues, please email covilloocko@gmail.com instead of using the issue tracker.


## Credits

- [Louco COVIL][ http://www.linkedin.com/in/loucov]


## License

To be filled by LouCov
