<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Identifiable;
use App\Traits\HasId;


class Customer implements Identifiable
{
    use HasId;

    public function __construct(
        private string $name,
        private string $phone,
        private ?string $address = null,
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Customer name cannot be empty.');
        }
        if (trim($phone) === '') {
            throw new \InvalidArgumentException('Customer phone cannot be empty.');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Customer name cannot be empty.');
        }
        $this->name = $name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        if (trim($phone) === '') {
            throw new \InvalidArgumentException('Customer phone cannot be empty.');
        }
        $this->phone = $phone;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }
}
