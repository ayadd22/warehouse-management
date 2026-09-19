<?php

declare(strict_types=1);

namespace App\Models;


class ProductSupplier
{
    public function __construct(
        private int $productId,
        private int $supplierId,
        private float $unitPrice,
    ) {
        if ($productId <= 0) {
            throw new \InvalidArgumentException('productId must be a positive integer.');
        }
        if ($supplierId <= 0) {
            throw new \InvalidArgumentException('supplierId must be a positive integer.');
        }
        if ($unitPrice < 0) {
            throw new \InvalidArgumentException('unitPrice cannot be negative.');
        }
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

    public function getSupplierId(): int
    {
        return $this->supplierId;
    }

    public function setSupplierId(int $supplierId): void
    {
        if ($supplierId <= 0) {
            throw new \InvalidArgumentException('supplierId must be a positive integer.');
        }
        $this->supplierId = $supplierId;
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
}
