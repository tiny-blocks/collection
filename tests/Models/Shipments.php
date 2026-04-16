<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

use TinyBlocks\Collection\Collection;
use TinyBlocks\Mapper\KeyPreservation;

final class Shipments extends Collection
{
    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        return Collection::createFrom(elements: $this)
            ->map(transformations: static fn(Shipment $shipment): array => $shipment->toArray())
            ->toArray();
    }
}
