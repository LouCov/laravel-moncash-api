
<p align="center">
    <img src="https://raw.githubusercontent.com/LouCov/laravel-moncash-api/dev/.github/assets/logo.png" alt="loucov"/>
</p>


# Laravel MonCash API

A Laravel package to integrate the Digicel Haiti **MonCash** payment gateway:
create payments, look up transactions, and send transfers — all behind a
clean, injectable API with automatic OAuth token caching.


## Installation

```bash
composer require loucov/laravel-moncash-api
```

Laravel auto-discovers the service provider and the `MoncashApi` facade. No
manual registration is needed.

**Environment variables are wired up automatically.** During the
`package:discover` step that Laravel runs after every `composer install`,
`composer update`, or `composer dump-autoload`, the package will append its
environment variables to your `.env` and `.env.example` files. The write is:

- **idempotent** — existing keys are never modified
- **atomic** — uses a temp file + `rename()`
- **permission-preserving** — the mode on `.env` (typically `0600`) is kept

After installation, review the required values in your `.env` file:

```dotenv
MONCASH_CLIENT_ID=your-client-id    # required
MONCASH_SECRET_KEY=your-secret-key  # required
MONCASH_SANDBOX=true                # required — seeded to `true`, flip to `false` for live mode
```

> `MONCASH_SANDBOX` is seeded with a safe default of `true` so fresh installs
> always point at the sandbox gateway. Flipping it to `false` is an explicit
> decision you should make before going to production.

If you ever want to re-run the installer manually — for example on a fresh
clone where `.env` was just created from `.env.example` — use:

```bash
php artisan moncash:install
```

### Publishing the config

The installer command also publishes the config. To publish it on its own:

```bash
php artisan vendor:publish --tag=moncash-config
```

### All available variables

| Variable | Required | Default | Purpose |
|---|---|---|---|
| `MONCASH_CLIENT_ID` | **yes** | — | OAuth client id |
| `MONCASH_SECRET_KEY` | **yes** | — | OAuth client secret |
| `MONCASH_SANDBOX` | **yes** | `true` | `true` for sandbox, `false` for live mode |
| `MONCASH_BUSINESS_KEY` | no | — | Optional business key |
| `MONCASH_HTTP_TIMEOUT` | no | `15` | HTTP timeout (seconds) |
| `MONCASH_HTTP_RETRIES` | no | `2` | Retries on transient failures |
| `MONCASH_HTTP_RETRY_WAIT` | no | `200` | Wait between retries (ms) |
| `MONCASH_CACHE_STORE` | no | default store | Cache store for the OAuth token |


## Usage

You can use the package in three equivalent ways. Pick whichever fits your
codebase best.

### 1. Facade

```php
use LouCov\LaravelMonCashApi\Facades\MoncashApi;

$response = MoncashApi::payment(1000, 'ORDER-123');

return redirect($response->redirectUrl);
```

### 2. Dependency injection

```php
use LouCov\LaravelMonCashApi\MoncashApi;

public function checkout(MoncashApi $moncash)
{
    $response = $moncash->payment(1000, 'ORDER-123');

    return redirect($response->redirectUrl);
}
```

### 3. Container resolution

```php
$moncash = app(\LouCov\LaravelMonCashApi\MoncashApi::class);
$response = $moncash->payment(1000, 'ORDER-123');
```

### Available methods

```php
// Create a new payment and get the hosted-page redirect URL.
$payment = $moncash->payment(int $amount, string $orderId);
$payment->redirectUrl;   // string — redirect the user here
$payment->token();       // string — the payment token
$payment->toArray();     // array  — full response

// Look up a transaction / payment.
$tx = $moncash->paymentDetailsByTransactionId(string $transactionId);
$tx = $moncash->paymentDetailsByOrderId(string $orderId);
$tx->transactionId();
$tx->reference();
$tx->cost();
$tx->payer();

// Send money from the business wallet to a MonCash account.
$transfer = $moncash->transfer(int $amount, string $receiver, string $desc);
$transfer->transactionId();
```

### Error handling

All failures throw typed exceptions — no more mixed response shapes:

```php
use LouCov\LaravelMonCashApi\Exceptions\MoncashException;
use LouCov\LaravelMonCashApi\Exceptions\AuthenticationException;
use LouCov\LaravelMonCashApi\Exceptions\MoncashConnectionException;
use LouCov\LaravelMonCashApi\Exceptions\MoncashRequestException;

try {
    $response = MoncashApi::payment(1000, 'ORDER-123');
} catch (AuthenticationException $e) {
    // Wrong MONCASH_CLIENT_ID / MONCASH_SECRET_KEY.
} catch (MoncashConnectionException $e) {
    // Network / TLS / timeout error.
} catch (MoncashRequestException $e) {
    // The API returned a non-success status.
    report($e);                // context() carries the raw API body
} catch (MoncashException $e) {
    // Catch-all for any other MonCash package error.
}
```


## Security

If you discover any security related issues, please email
covilloocko@gmail.com instead of using the issue tracker.


## Credits

- [Louco COVIL](http://www.linkedin.com/in/loucov)


## License

MIT. See [LICENSE](LICENSE).
