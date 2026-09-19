<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CustomerRepositoryInterface;
use App\Models\Customer;
use InvalidArgumentException;
use RuntimeException;


class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
    ) {}

    
    public function create(string $name, string $phone, ?string $address = null): Customer
    {
        $name    = trim($name);
        $phone   = trim($phone);
        $address = $address !== null ? trim($address) : null;

        if ($name === '') {
            throw new InvalidArgumentException('Customer name cannot be empty.');
        }
        if ($phone === '') {
            throw new InvalidArgumentException('Customer phone cannot be empty.');
        }

        $customer = new Customer(
            name:    $name,
            phone:   $phone,
            address: $address !== '' ? $address : null,
        );

        $this->customerRepository->create($customer);

        return $customer;
    }

    
    public function update(int $id, string $name, string $phone, ?string $address = null): Customer
    {
        $name    = trim($name);
        $phone   = trim($phone);
        $address = $address !== null ? trim($address) : null;

        if ($name === '') {
            throw new InvalidArgumentException('Customer name cannot be empty.');
        }
        if ($phone === '') {
            throw new InvalidArgumentException('Customer phone cannot be empty.');
        }

        $customer = $this->customerRepository->findById($id);
        if ($customer === null) {
            throw new RuntimeException(sprintf('Customer #%d not found.', $id));
        }

        $customer->setName($name);
        $customer->setPhone($phone);
        $customer->setAddress($address !== '' ? $address : null);

        $this->customerRepository->update($customer);

        return $customer;
    }

    
    public function delete(int $id): void
    {
        if ($this->customerRepository->findById($id) === null) {
            throw new RuntimeException(sprintf('Customer #%d not found.', $id));
        }

        $this->customerRepository->delete($id);
    }

   
    public function findById(int $id): Customer
    {
        $customer = $this->customerRepository->findById($id);
        if ($customer === null) {
            throw new RuntimeException(sprintf('Customer #%d not found.', $id));
        }

        return $customer;
    }

    
    public function findAll(): array
    {
        return $this->customerRepository->findAll();
    }
}
