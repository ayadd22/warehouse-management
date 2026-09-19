<?php

declare(strict_types=1);

namespace App\Contracts;


interface Identifiable
{
    public function getId(): ?int;

    public function setId(int $id): void;
}
