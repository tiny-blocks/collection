<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

final readonly class Product
{
    public function __construct(public string $name, public Amount $amount)
    {
    }
}
