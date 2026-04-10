<?php

namespace LouCov\LaravelMonCashApi\Exceptions;

/**
 * Raised when the HTTP transport to MonCash fails (DNS, timeout, TLS...).
 */
class MoncashConnectionException extends MoncashException
{
}
