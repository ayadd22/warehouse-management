<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Identifiable;
use App\Enums\MovementType;
use App\Traits\HasId;


class StockMovement implements Identifiable
{
    use HasId;

    private \DateTimeImmutable $createdAt;

    public function __construct(
        private int $productId,
        private MovementType $type,
        private float $quantity,
        private float $unitPrice,
        private ?int $supplierId = null,
        private ?int $customerId = null,
        ?\DateTimeImmutable $createdAt = null,
    ) {
        if ($productId <= 0) {
            throw new \InvalidArgumentException('productId must be a positive integer.');
        }
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('quantity must be greater than zero.');
        }
        if ($unitPrice < 0) {
            throw new \InvalidArgumentException('unitPrice cannot be negative.');
        }

        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): void
    {
        if ($productId <= 0) {
            throw new \InvalidArgumentException('productId must be a positive integer.');
        }
        $this->productId = $productId;
    }

    public function getType(): MovementType
    {
        return $this->type;
    }

    public function setType(MovementType $type): void
    {
        $this->type = $type;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('quantity must be greater than zero.');
        }
        $this->quantity = $quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): void
    {
        if ($unitPrice < 0) {
            throw new \InvalidArgumentException('unitPrice cannot be negative.');
        }
        $this->unitPrice = $unitPrice;
    }

    public function getSupplierId(): ?int
    {
        return $this->supplierId;
    }

    public function setSupplierId(?int $supplierId): void
    {
        $this->supplierId = $supplierId;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function setCustomerId(?int $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
