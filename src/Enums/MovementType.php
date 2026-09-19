<?php

declare(strict_types=1);

namespace App\Enums;

enum MovementType: string
{
    case Supply = 'supply';
    case Sale   = 'sale';
}
