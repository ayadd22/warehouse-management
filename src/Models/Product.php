<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Identifiable;
use App\Traits\HasId;


class Product implements Identifiable
{
    use HasId;

    public function __construct(
        private string $name,
        private string $unit,
        private ?string $description = null,
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Product name cannot be empty.');
        }
        if (trim($unit) === '') {
            throw new \InvalidArgumentException('Product unit cannot be empty.');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Product name cannot be empty.');
        }
        $this->name = $name;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): void
    {
        if (trim($unit) === '') {
            throw new \InvalidArgumentException('Product unit cannot be empty.');
        }
        $this->unit = $unit;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
