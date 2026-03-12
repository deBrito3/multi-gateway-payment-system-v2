<?php

namespace App\Exceptions;

use Exception;

class GatewayAuthException extends Exception
{
    public function __construct(string $gateway, string $reason = '')
    {
        parent::__construct("Authentication failed for gateway: {$gateway}. {$reason}");
    }
}
