<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Supplier;

interface SupplierRepositoryInterface
{
    
    public function create(Supplier $supplier): void;

    
    public function findById(int $id): ?Supplier;

    
    public function findByNameAndPhone(string $name, string $normalizedPhone): ?Supplier;

   
    public function findAll(): array;

    
    public function update(Supplier $supplier): void;

    
    public function delete(int $id): void;
}
