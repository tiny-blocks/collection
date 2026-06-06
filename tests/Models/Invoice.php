<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

final readonly class Invoice
{
    public function __construct(public string $id, public float $amount, public string $customer)
    {
    }
}
