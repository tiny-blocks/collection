<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

use TinyBlocks\Collection\Collection;

final class InvoiceSummaries extends Collection
{
    public function sumByCustomer(string $customer): float
    {
        return $this
            ->filter(predicates: fn(InvoiceSummary $summary): bool => $summary->customer === $customer)
            ->reduce(
                aggregator: fn(float $carry, InvoiceSummary $summary): float => $carry + $summary->amount,
                initial: 0.0
            );
    }
}
