<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

final class InvoiceSummary
{
    public function __construct(public float $amount, public string $customer)
    {
    }
}
