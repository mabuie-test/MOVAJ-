<?php

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected string $table;

    public function table(): string
    {
        return $this->table;
    }
}
