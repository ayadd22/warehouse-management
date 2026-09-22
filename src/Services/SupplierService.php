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
        $name           = trim($name);
        $normalizedPhone = self::normalizePhone($phone);
        $address        = $address !== null ? trim($address) : null;

     

      
        if ($this->supplierRepository->findByNameAndPhone($name, $normalizedPhone) !== null) {
            throw new RuntimeException(
                sprintf(
                    'A supplier with the name "%s" and phone "%s" already exists.',
                    $name,
                    $normalizedPhone
                )
            );
        }

        $supplier = new Supplier(
            name:    $name,
            phone:   $normalizedPhone,          // store normalized form
            address: $address !== '' ? $address : null,
        );

        $this->supplierRepository->create($supplier);

        return $supplier;
    }

    
    public function update(int $id, string $name, string $phone, ?string $address = null): Supplier
    {
        $name            = trim($name);
        $normalizedPhone = self::normalizePhone($phone);
        $address         = $address !== null ? trim($address) : null;

       
        $supplier = $this->supplierRepository->findById($id);
        if ($supplier === null) {
            throw new RuntimeException(sprintf('Supplier #%d not found.', $id));
        }

       
        if ($name !== $supplier->getName() || $normalizedPhone !== $supplier->getPhone()) {
            $existing = $this->supplierRepository->findByNameAndPhone($name, $normalizedPhone);
            if ($existing !== null && $existing->getId() !== $id) {
                throw new RuntimeException(
                    sprintf(
                        'A supplier with the name "%s" and phone "%s" already exists.',
                        $name,
                        $normalizedPhone
                    )
                );
            }
        }

        $supplier->setName($name);
        $supplier->setPhone($normalizedPhone);
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


    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone) ?? '';
    }
}
