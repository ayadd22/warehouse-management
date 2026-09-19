<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use PDO;


class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    
    public function create(Product $product): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (name, unit, description)
             VALUES (:name, :unit, :description)'
        );

        $stmt->execute([
            ':name'        => $product->getName(),
            ':unit'        => $product->getUnit(),
            ':description' => $product->getDescription(),
        ]);

        $product->setId((int) $this->pdo->lastInsertId());
    }

    
    public function findById(int $id): ?Product
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, unit, description
             FROM products
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row !== false ? $this->mapRow($row) : null;
    }

    
    public function findByName(string $name): ?Product
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, unit, description
             FROM products
             WHERE name = :name'
        );
        $stmt->execute([':name' => $name]);

        $row = $stmt->fetch();

        return $row !== false ? $this->mapRow($row) : null;
    }

    
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, unit, description
             FROM products
             ORDER BY name'
        );

        return array_map(
            fn(array $row) => $this->mapRow($row),
            $stmt->fetchAll()
        );
    }

    public function update(Product $product): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE products
             SET name        = :name,
                 unit        = :unit,
                 description = :description
             WHERE id = :id'
        );

        $stmt->execute([
            ':name'        => $product->getName(),
            ':unit'        => $product->getUnit(),
            ':description' => $product->getDescription(),
            ':id'          => $product->getId(),
        ]);
    }

  
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    private function mapRow(array $row): Product
    {
        $product = new Product(
            name:        $row['name'],
            unit:        $row['unit'],
            description: $row['description'],
        );
        $product->setId((int) $row['id']);

        return $product;
    }
}
