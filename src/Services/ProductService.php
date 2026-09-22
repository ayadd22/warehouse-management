<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use InvalidArgumentException;
use RuntimeException;


class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function create(string $name, string $unit, ?string $description = null): Product
    {
        $name        = trim($name);
        $unit        = trim($unit);
        $description = $description !== null ? trim($description) : null;

       
        
        if ($this->productRepository->findByName($name) !== null) {
            throw new RuntimeException(
                sprintf('A product with the name "%s" already exists.', $name)
            );
        }

        $product = new Product(
            name:        $name,
            unit:        $unit,
            description: $description !== '' ? $description : null,
        );

        $this->productRepository->create($product);

        return $product;
    }

   
    public function update(int $id, string $name, string $unit, ?string $description = null): Product
    {
        $name        = trim($name);
        $unit        = trim($unit);
        $description = $description !== null ? trim($description) : null;

        

        $product = $this->productRepository->findById($id);
        if ($product === null) {
            throw new RuntimeException(sprintf('Product #%d not found.', $id));
        }

        if ($name !== $product->getName()) {
            $existing = $this->productRepository->findByName($name);
            if ($existing !== null && $existing->getId() !== $id) {
                throw new RuntimeException(
                    sprintf('A product with the name "%s" already exists.', $name)
                );
            }
        }

        $product->setName($name);
        $product->setUnit($unit);
        $product->setDescription($description !== '' ? $description : null);

        $this->productRepository->update($product);

        return $product;
    }

    
    public function delete(int $id): void
    {
        if ($this->productRepository->findById($id) === null) {
            throw new RuntimeException(sprintf('Product #%d not found.', $id));
        }

        $this->productRepository->delete($id);
    }

    
    public function findById(int $id): Product
    {
        $product = $this->productRepository->findById($id);
        if ($product === null) {
            throw new RuntimeException(sprintf('Product #%d not found.', $id));
        }

        return $product;
    }

  
    public function findAll(): array
    {
        return $this->productRepository->findAll();
    }
}
