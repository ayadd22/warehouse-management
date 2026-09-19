<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Product;

interface ProductRepositoryInterface
{
   
    public function create(Product $product): void;

   
    public function findById(int $id): ?Product;

   
    public function findByName(string $name): ?Product;

   
    public function findAll(): array;

    
    public function update(Product $product): void;

    public function delete(int $id): void;
}
