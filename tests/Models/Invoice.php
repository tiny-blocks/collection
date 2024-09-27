<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

final class Invoice
{
    public function __construct(public string $id, public float $amount, public string $customer)
    {
    }
}
