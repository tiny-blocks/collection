<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

use TinyBlocks\Collection\Collection;

final class Invoices extends Collection
{
    public function totalAmount(): float
    {
        return $this->reduce(
            accumulator: static fn(float $carry, Invoice $invoice): float => $carry + $invoice->amount,
            initial: 0.0
        );
    }

    public function forCustomer(string $customer): static
    {
        return $this->filter(
            predicates: static fn(Invoice $invoice): bool => $invoice->customer === $customer
        );
    }
}
