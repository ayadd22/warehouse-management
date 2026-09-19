<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SupplierRepositoryInterface;
use App\Models\Supplier;
use InvalidArgumentException;
use RuntimeException;


class SupplierService
{
    public function __construct(
        private readonly SupplierRepositoryInterface $supplierRepository,
    ) {}

  
    public function create(string $name, string $phone, ?string $address = null): Supplier
    {
        $name    = trim($name);
        $phone   = trim($phone);
        $address = $address !== null ? trim($address) : null;

        if ($name === '') {
            throw new InvalidArgumentException('Supplier name cannot be empty.');
        }
        if ($phone === '') {
            throw new InvalidArgumentException('Supplier phone cannot be empty.');
        }

        $supplier = new Supplier(
            name:    $name,
            phone:   $phone,
            address: $address !== '' ? $address : null,
        );

        $this->supplierRepository->create($supplier);

        return $supplier;
    }

    
    public function update(int $id, string $name, string $phone, ?string $address = null): Supplier
    {
        $name    = trim($name);
        $phone   = trim($phone);
        $address = $address !== null ? trim($address) : null;

        if ($name === '') {
            throw new InvalidArgumentException('Supplier name cannot be empty.');
        }
        if ($phone === '') {
            throw new InvalidArgumentException('Supplier phone cannot be empty.');
        }

        $supplier = $this->supplierRepository->findById($id);
        if ($supplier === null) {
            throw new RuntimeException(sprintf('Supplier #%d not found.', $id));
        }

        $supplier->setName($name);
        $supplier->setPhone($phone);
        $supplier->setAddress($address !== '' ? $address : null);

        $this->supplierRepository->update($supplier);

        return $supplier;
    }

 
    public function delete(int $id): void
    {
        if ($this->supplierRepository->findById($id) === null) {
            throw new RuntimeException(sprintf('Supplier #%d not found.', $id));
        }

        $this->supplierRepository->delete($id);
    }

    public function findById(int $id): Supplier
    {
        $supplier = $this->supplierRepository->findById($id);
        if ($supplier === null) {
            throw new RuntimeException(sprintf('Supplier #%d not found.', $id));
        }

        return $supplier;
    }

    public function findAll(): array
    {
        return $this->supplierRepository->findAll();
    }
}
