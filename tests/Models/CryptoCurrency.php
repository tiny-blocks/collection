<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

final readonly class CryptoCurrency
{
    public function __construct(public string $name, public float $price, public string $symbol)
    {
    }
}
