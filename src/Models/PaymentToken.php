<?php

namespace LouCov\LaravelMonCashApi\Models;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Immutable DTO representing the `payment_token` object returned by the
 * MonCash CreatePayment endpoint.
 *
 * Dates are stored as raw strings (e.g. "2019-05-24 12:46:55:107") and
 * exposed as Carbon instances via `createdAt()` / `expiredAt()`.
 */
final class PaymentToken
{
    /**
     * Date format used by the MonCash API: "YYYY-MM-DD HH:mm:ss:mmm"
     * where the last segment is milliseconds.
     */
    private const DATE_FORMAT = 'Y-m-d H:i:s:v';

    public function __construct(
        public readonly string $token,
        public readonly string $created,
        public readonly string $expired,
    ) {
        if (trim($token) === '') {
            throw new InvalidArgumentException('Payment token cannot be empty.');
        }
    }

    /**
     * Build a PaymentToken from the raw `payment_token` array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            token:   (string) ($data['token']   ?? ''),
            created: (string) ($data['created'] ?? ''),
            expired: (string) ($data['expired'] ?? ''),
        );
    }

    /**
     * Creation date as a Carbon instance.
     */
    public function createdAt(): Carbon
    {
        return Carbon::createFromFormat(self::DATE_FORMAT, $this->created)
            ?? Carbon::parse($this->created);
    }

    /**
     * Expiration date as a Carbon instance.
     */
    public function expiredAt(): Carbon
    {
        return Carbon::createFromFormat(self::DATE_FORMAT, $this->expired)
            ?? Carbon::parse($this->expired);
    }

    /**
     * Whether the token is already past its expiration date.
     */
    public function isExpired(): bool
    {
        return $this->expiredAt()->isPast();
    }

    /**
     * Seconds remaining before the token expires (negative when already expired).
     */
    public function secondsUntilExpiry(): int
    {
        return (int) now()->diffInSeconds($this->expiredAt(), absolute: false);
    }

    /**
     * @return array{token: string, created: string, expired: string}
     */
    public function toArray(): array
    {
        return [
            'token'   => $this->token,
            'created' => $this->created,
            'expired' => $this->expired,
        ];
    }
}
