<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\SupplierRepositoryInterface;
use App\Models\Supplier;
use PDO;


class SupplierRepository implements SupplierRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    
    public function create(Supplier $supplier): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO suppliers (name, phone, address)
             VALUES (:name, :phone, :address)'
        );

        $stmt->execute([
            ':name'    => $supplier->getName(),
            ':phone'   => $supplier->getPhone(),
            ':address' => $supplier->getAddress(),
        ]);

        $supplier->setId((int) $this->pdo->lastInsertId());
    }

    public function findById(int $id): ?Supplier
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, phone, address
             FROM suppliers
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
             FROM suppliers
             ORDER BY name'
        );

        return array_map(
            fn(array $row) => $this->mapRow($row),
            $stmt->fetchAll()
        );
    }

    
    public function update(Supplier $supplier): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE suppliers
             SET name    = :name,
                 phone   = :phone,
                 address = :address
             WHERE id = :id'
        );

        $stmt->execute([
            ':name'    => $supplier->getName(),
            ':phone'   => $supplier->getPhone(),
            ':address' => $supplier->getAddress(),
            ':id'      => $supplier->getId(),
        ]);
    }

    
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM suppliers WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

   
    private function mapRow(array $row): Supplier
    {
        $supplier = new Supplier(
            name:    $row['name'],
            phone:   $row['phone'],
            address: $row['address'],
        );
        $supplier->setId((int) $row['id']);

        return $supplier;
    }
}
