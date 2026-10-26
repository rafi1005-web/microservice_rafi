<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ServiceUnavailableException extends RuntimeException
{
    public function __construct(string $serviceName, ?Throwable $previous = null)
    {
        parent::__construct("{$serviceName} tidak dapat dihubungi, coba lagi nanti", 0, $previous);
    }
}
