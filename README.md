
<p align="center">
    <img src="https://raw.githubusercontent.com/LouCov/laravel-moncash-api/dev/.github/assets/logo.png" alt="loucov"/>
</p>


# Laravel MonCash API

A Laravel package to integrate the Digicel Haiti **MonCash** payment gateway:
create payments, look up transactions, and send transfers — all behind a
clean, injectable API.


## Installation

```bash
composer require loucov/laravel-moncash-api
```

Laravel auto-discovers the service provider and the `MoncashApi` facade. No
manual registration is needed.

**Everything is wired up automatically.** During the `package:discover` step
that Laravel runs after every `composer install` / `composer update`, the package:

- publishes `config/moncash.php` to your application (only if it doesn't exist yet)
- appends its environment variables to your `.env` and `.env.example` files
- registers a `pre-package-uninstall` Composer hook so removal is equally automatic

All writes are **idempotent**, **atomic** (temp file + `rename()`), and
**permission-preserving**.

After installation, fill in the required values in your `.env` file:

```dotenv
MONCASH_CLIENT_ID=your-client-id    # required
MONCASH_SECRET_KEY=your-secret-key  # required
MONCASH_SANDBOX=true                # required — flip to `false` for live mode
```

> `MONCASH_SANDBOX` defaults to `true` so fresh installs always point at the
> sandbox gateway. Flipping it to `false` is an explicit decision you should
> make before going to production.

If you ever want to re-run the installer manually — for example on a fresh
clone where `.env` was just created from `.env.example` — use:

```bash
php artisan moncash:install
```

### Publishing the config

The config is published automatically on install. To publish or re-publish it
manually:

```bash
# publish (skip if the file already exists)
php artisan vendor:publish --tag=moncash-config

# overwrite an existing published config
php artisan vendor:publish --tag=moncash-config --force
```

### Available environment variables

| Variable | Required | Default | Purpose |
|---|---|---|---|
| `MONCASH_CLIENT_ID` | **yes** | — | OAuth client id |
| `MONCASH_SECRET_KEY` | **yes** | — | OAuth client secret |
| `MONCASH_SANDBOX` | **yes** | `true` | `true` for sandbox, `false` for live |
| `MONCASH_BUSINESS_KEY` | no | — | Optional business key |
| `MONCASH_HTTP_TIMEOUT` | no | `15` | HTTP timeout (seconds) |
| `MONCASH_HTTP_RETRIES` | no | `2` | Retries on transient network failures |
| `MONCASH_HTTP_RETRY_WAIT` | no | `200` | Wait between retries (ms) |


## Uninstallation

Removal is **automatic**. When you run:

```bash
composer remove loucov/laravel-moncash-api
```

Composer fires the registered `pre-package-uninstall` hook before deleting the
vendor files. The hook:

1. Removes `config/moncash.php`
2. Removes all `MONCASH_*` variables from `.env` and `.env.example`
3. Removes itself from your `composer.json`

To trigger the same cleanup manually (without removing the package), run:

```bash
php artisan moncash:uninstall
```


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

#### `payment(int $amount, string $orderId): PaymentResponse`

Create a new payment request. Redirects the customer to the MonCash hosted
payment page.

```php
$payment = $moncash->payment(1000, 'ORDER-123');

$payment->redirectUrl;      // string  — redirect the user here
$payment->token();          // string  — the payment token
$payment->mode;             // string  — 'sandbox' | 'live'
$payment->path;             // string  — gateway path used
$payment->timestamp;        // int     — Unix timestamp of the response
$payment->toArray();        // array   — structured response body
$payment->raw;              // array   — raw API response
```

#### `paymentDetailsByTransactionId(string $transactionId): TransactionResponse`
#### `paymentDetailsByOrderId(string $orderId): TransactionResponse`

Look up a completed payment.

```php
$tx = $moncash->paymentDetailsByTransactionId('TXN-456');
// or
$tx = $moncash->paymentDetailsByOrderId('ORDER-123');

$tx->transactionId();   // ?string
$tx->reference();       // ?string
$tx->cost();            // ?int    — amount in HTG
$tx->message();         // ?string — gateway status message
$tx->payer();           // ?string — payer's MonCash number
$tx->toArray();         // array
$tx->raw;               // array   — raw API response
```

#### `transfer(int $amount, string $receiver, string $desc = ''): TransferResponse`

Send money from the business wallet to a MonCash account.

```php
$transfer = $moncash->transfer(500, '509-xxxx-xxxx', 'Salary');

$transfer->transactionId();  // ?string
$transfer->amount();         // ?int    — amount transferred
$transfer->receiver();       // ?string — recipient's MonCash number
$transfer->toArray();        // array
$transfer->raw;              // array   — raw API response
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
    $e->context(); // carries the raw API response body
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
