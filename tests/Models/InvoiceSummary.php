<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

final class InvoiceSummary
{
    public function __construct(public float $amount, public string $customer)
    {
    }
}
