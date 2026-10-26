<?php

namespace App\Exceptions;

use RuntimeException;

class EmptyCartException extends RuntimeException
{
    public function __construct(int $userId)
    {
        parent::__construct("Keranjang user {$userId} kosong");
    }
}
