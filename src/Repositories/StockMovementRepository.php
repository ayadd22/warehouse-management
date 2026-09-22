<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\StockMovementRepositoryInterface;
use App\Enums\MovementType;
use App\Models\StockMovement;
use PDO;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    public function create(StockMovement $movement): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO stock_movements
                 (product_id, type, quantity, unit_price, supplier_id, customer_id, created_at)
             VALUES
                 (:product_id, :type, :quantity, :unit_price, :supplier_id, :customer_id, :created_at)'
        );

        $stmt->execute([
            ':product_id'  => $movement->getProductId(),
            ':type'        => $movement->getType()->value,    // 'supply' | 'sale'
            ':quantity'    => $movement->getQuantity(),
            ':unit_price'  => $movement->getUnitPrice(),      // string → DECIMAL(12,2)
            ':supplier_id' => $movement->getSupplierId(),
            ':customer_id' => $movement->getCustomerId(),
            ':created_at'  => $movement->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $movement->setId((int) $this->pdo->lastInsertId());
    }

    public function findById(int $id): ?StockMovement
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, product_id, type, quantity, unit_price,
                    supplier_id, customer_id, created_at
             FROM stock_movements
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row !== false ? $this->mapRow($row) : null;
    }

    
    public function findByProductId(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, product_id, type, quantity, unit_price,
                    supplier_id, customer_id, created_at
             FROM stock_movements
             WHERE product_id = :product_id
             ORDER BY created_at ASC, id ASC'
        );
        $stmt->execute([':product_id' => $productId]);

        return array_map(
            fn(array $row) => $this->mapRow($row),
            $stmt->fetchAll()
        );
    }

    public function findByProductIdAndType(int $productId, MovementType $type): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, product_id, type, quantity, unit_price,
                    supplier_id, customer_id, created_at
             FROM stock_movements
             WHERE product_id = :product_id
               AND type       = :type
             ORDER BY created_at ASC, id ASC'
        );
        $stmt->execute([
            ':product_id' => $productId,
            ':type'       => $type->value,
        ]);

        return array_map(
            fn(array $row) => $this->mapRow($row),
            $stmt->fetchAll()
        );
    }

    
    public function getCurrentStock(int $productId): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                 COALESCE(SUM(CASE WHEN type = 'supply' THEN quantity ELSE 0 END), 0)
               - COALESCE(SUM(CASE WHEN type = 'sale'   THEN quantity ELSE 0 END), 0)
                 AS current_stock
             FROM stock_movements
             WHERE product_id = :product_id"
        );
        $stmt->execute([':product_id' => $productId]);

        $row = $stmt->fetch();

        return (float) ($row['current_stock'] ?? 0.0);
    }

    private function mapRow(array $row): StockMovement
    {
        $movement = new StockMovement(
            productId:  (int)    $row['product_id'],
            type:       MovementType::from($row['type']),
            quantity:   (float)  $row['quantity'],
            unitPrice:  (string) $row['unit_price'],  
            supplierId: $row['supplier_id'] !== null ? (int) $row['supplier_id'] : null,
            customerId: $row['customer_id'] !== null ? (int) $row['customer_id'] : null,
            createdAt:  new \DateTimeImmutable($row['created_at']),
        );
        $movement->setId((int) $row['id']);

        return $movement;
    }
}
