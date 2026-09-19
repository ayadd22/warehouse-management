<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\CustomerRepositoryInterface;
use App\Models\Customer;
use PDO;


class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    
    public function create(Customer $customer): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO customers (name, phone, address)
             VALUES (:name, :phone, :address)'
        );

        $stmt->execute([
            ':name'    => $customer->getName(),
            ':phone'   => $customer->getPhone(),
            ':address' => $customer->getAddress(),
        ]);

        $customer->setId((int) $this->pdo->lastInsertId());
    }

    public function findById(int $id): ?Customer
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, phone, address
             FROM customers
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row !== false ? $this->mapRow($row) : null;
    }

    
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, phone, address
             FROM customers
             ORDER BY name'
        );

        return array_map(
            fn(array $row) => $this->mapRow($row),
            $stmt->fetchAll()
        );
    }

    
    public function update(Customer $customer): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE customers
             SET name    = :name,
                 phone   = :phone,
                 address = :address
             WHERE id = :id'
        );

        $stmt->execute([
            ':name'    => $customer->getName(),
            ':phone'   => $customer->getPhone(),
            ':address' => $customer->getAddress(),
            ':id'      => $customer->getId(),
        ]);
    }

    
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM customers WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    
    private function mapRow(array $row): Customer
    {
        $customer = new Customer(
            name:    $row['name'],
            phone:   $row['phone'],
            address: $row['address'],
        );
        $customer->setId((int) $row['id']);

        return $customer;
    }
}
