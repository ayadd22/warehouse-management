<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CustomerRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Contracts\StockMovementRepositoryInterface;
use App\Contracts\SupplierRepositoryInterface;
use App\Enums\MovementType;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStockMovementException;
use App\Models\StockMovement;


class StockMovementService
{
    public function __construct(
        private readonly StockMovementRepositoryInterface $stockMovementRepository,
        private readonly ProductRepositoryInterface       $productRepository,
        private readonly SupplierRepositoryInterface      $supplierRepository,
        private readonly CustomerRepositoryInterface      $customerRepository,
    ) {}

   
    public function supply(int    $productId,float  $quantity,string $unitPrice,int    $supplierId,): StockMovement {
        if ($quantity <= 0) {
            throw new InvalidStockMovementException(
                'Supply quantity must be greater than zero.'
            );
        }

        if (!self::isValidPrice($unitPrice)) {
            throw new InvalidStockMovementException(
                'Supply unit price must be a non-negative decimal number (e.g. "150.00").'
            );
        }

        if ($this->productRepository->findById($productId) === null) {
            throw new InvalidStockMovementException(
                sprintf('Product #%d not found.', $productId)
            );
        }

        if ($this->supplierRepository->findById($supplierId) === null) {
            throw new InvalidStockMovementException(
                sprintf('Supplier #%d not found.', $supplierId)
            );
        }

        $movement = new StockMovement(
            productId:  $productId,
            type:       MovementType::Supply,
            quantity:   $quantity,
            unitPrice:  $unitPrice,
            supplierId: $supplierId,
            customerId: null,           
        );

        $this->stockMovementRepository->create($movement);

        return $movement;
    }

    
    public function sell( int    $productId,    float  $quantity,   string $unitPrice,   int    $customerId, ): StockMovement {
        if ($quantity <= 0) {
            throw new InvalidStockMovementException(
                'Sale quantity must be greater than zero.'
            );
        }

        if (!self::isValidPrice($unitPrice)) {
            throw new InvalidStockMovementException(
                'Sale unit price must be a non-negative decimal number (e.g. "200.00").'
            );
        }

        if ($this->productRepository->findById($productId) === null) {
            throw new InvalidStockMovementException(
                sprintf('Product #%d not found.', $productId)
            );
        }

        if ($this->customerRepository->findById($customerId) === null) {
            throw new InvalidStockMovementException(
                sprintf('Customer #%d not found.', $customerId)
            );
        }

     
        $availableStock = $this->stockMovementRepository->getCurrentStock($productId);

        if ($quantity > $availableStock) {
           
            throw new InsufficientStockException($productId, $quantity, $availableStock);
        }

        $movement = new StockMovement(
            productId:  $productId,
            type:       MovementType::Sale,
            quantity:   $quantity,
            unitPrice:  $unitPrice,
            supplierId: null,          
            customerId: $customerId,
        );

        $this->stockMovementRepository->create($movement);

        return $movement;
    }

   
    public function findById(int $id): StockMovement
    {
        $movement = $this->stockMovementRepository->findById($id);
        if ($movement === null) {
            throw new \RuntimeException(sprintf('StockMovement #%d not found.', $id));
        }

        return $movement;
    }

    public function findByProductId(int $productId): array
    {
        return $this->stockMovementRepository->findByProductId($productId);
    }

 
    public function getCurrentStock(int $productId): float
    {
        return $this->stockMovementRepository->getCurrentStock($productId);
    }

  
    private static function isValidPrice(string $value): bool
    {
        return (bool) preg_match('/^\d{1,10}(\.\d{1,2})?$/', $value);
    }
}
