<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Enums\MovementType;
use App\Models\StockMovement;

interface StockMovementRepositoryInterface
{
   
    public function create(StockMovement $movement): void;

   
    public function findById(int $id): ?StockMovement;

  
    public function findByProductId(int $productId): array;

   
    public function findByProductIdAndType(int $productId, MovementType $type): array;

  
    public function getCurrentStock(int $productId): float;
}
