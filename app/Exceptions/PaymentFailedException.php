<?php

namespace App\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    public function __construct(string $message = 'All payment gateways failed')
    {
        parent::__construct($message);
    }
}
