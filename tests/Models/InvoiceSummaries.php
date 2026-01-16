<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

use TinyBlocks\Collection\Collection;

final class InvoiceSummaries extends Collection
{
    public function sumByCustomer(string $customer): float
    {
        return $this
            ->filter(predicates: static fn(InvoiceSummary $summary): bool => $summary->customer === $customer)
            ->reduce(
                aggregator: static fn(float $carry, InvoiceSummary $summary): float => $carry + $summary->amount,
                initial: 0.0
            );
    }
}
