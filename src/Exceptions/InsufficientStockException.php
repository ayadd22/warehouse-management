<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        int   $productId,
        float $requested,
        float $available,
    ) {
        parent::__construct(sprintf(
            'Insufficient stock for product #%d: requested %.3f, available %.3f.',
            $productId,
            $requested,
            $available,
        ));
    }
}
