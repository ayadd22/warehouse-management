<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Customer;

interface CustomerRepositoryInterface
{
   
    public function create(Customer $customer): void;

   
    public function findById(int $id): ?Customer;

  
    public function findAll(): array;

    
    public function update(Customer $customer): void;

    
    public function delete(int $id): void;
}
