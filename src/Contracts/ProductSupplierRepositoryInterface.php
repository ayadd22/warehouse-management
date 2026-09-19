<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\ProductSupplier;

interface ProductSupplierRepositoryInterface
{
  
    public function create(ProductSupplier $productSupplier): void;

   
    public function find(int $productId, int $supplierId): ?ProductSupplier;

    
    public function findByProductId(int $productId): array;

    public function findBySupplierId(int $supplierId): array;

   
    public function update(ProductSupplier $productSupplier): void;

    
    public function delete(int $productId, int $supplierId): void;
}
