<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ProductSupplierRepositoryInterface;
use App\Models\ProductSupplier;
use PDO;


class ProductSupplierRepository implements ProductSupplierRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    
    public function create(ProductSupplier $productSupplier): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO product_suppliers (product_id, supplier_id, unit_price)
             VALUES (:product_id, :supplier_id, :unit_price)'
        );

        $stmt->execute([
            ':product_id'  => $productSupplier->getProductId(),
            ':supplier_id' => $productSupplier->getSupplierId(),
            ':unit_price'  => $productSupplier->getUnitPrice(),
        ]);
    }

   
    public function find(int $productId, int $supplierId): ?ProductSupplier
    {
        $stmt = $this->pdo->prepare(
            'SELECT product_id, supplier_id, unit_price
             FROM product_suppliers
             WHERE product_id  = :product_id
               AND supplier_id = :supplier_id'
        );
        $stmt->execute([
            ':product_id'  => $productId,
            ':supplier_id' => $supplierId,
        ]);

        $row = $stmt->fetch();

        return $row !== false ? $this->mapRow($row) : null;
    }

   
    public function findByProductId(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT product_id, supplier_id, unit_price
             FROM product_suppliers
             WHERE product_id = :product_id'
        );
        $stmt->execute([':product_id' => $productId]);

        return array_map(
            fn(array $row) => $this->mapRow($row),
            $stmt->fetchAll()
        );
    }

   
    public function findBySupplierId(int $supplierId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT product_id, supplier_id, unit_price
             FROM product_suppliers
             WHERE supplier_id = :supplier_id'
        );
        $stmt->execute([':supplier_id' => $supplierId]);

        return array_map(
            fn(array $row) => $this->mapRow($row),
            $stmt->fetchAll()
        );
    }

   
    public function update(ProductSupplier $productSupplier): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE product_suppliers
             SET unit_price  = :unit_price
             WHERE product_id  = :product_id
               AND supplier_id = :supplier_id'
        );

        $stmt->execute([
            ':unit_price'  => $productSupplier->getUnitPrice(),
            ':product_id'  => $productSupplier->getProductId(),
            ':supplier_id' => $productSupplier->getSupplierId(),
        ]);
    }

   
    public function delete(int $productId, int $supplierId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM product_suppliers
             WHERE product_id  = :product_id
               AND supplier_id = :supplier_id'
        );
        $stmt->execute([
            ':product_id'  => $productId,
            ':supplier_id' => $supplierId,
        ]);
    }

   
    private function mapRow(array $row): ProductSupplier
    {
        return new ProductSupplier(
            productId:  (int)   $row['product_id'],
            supplierId: (int)   $row['supplier_id'],
            unitPrice:  (float) $row['unit_price'],
        );
    }
}
