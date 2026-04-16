<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

use TinyBlocks\Collection\Collection;

final readonly class ShipmentRecord
{
    private function __construct(private array $records)
    {
    }

    public static function fromRecords(array $records): ShipmentRecord
    {
        return new ShipmentRecord(records: $records);
    }

    public function toShipments(): Shipments
    {
        return Shipments::createFrom(
            elements: Collection::createFrom(elements: $this->records)
                ->map(transformations: static fn(array $record): Shipment => Shipment::from(
                    id: $record['id'],
                    status: $record['status'],
                    carrier: $record['carrier'],
                    createdAt: $record['created_at'],
                    customerId: $record['customer_id']
                ))
        );
    }

    public function toShipmentsFromClosure(): Shipments
    {
        return Shipments::createFromClosure(
            factory: fn(): Collection => Collection::createFrom(elements: $this->records)
                ->map(transformations: static fn(array $record): Shipment => Shipment::from(
                    id: $record['id'],
                    status: $record['status'],
                    carrier: $record['carrier'],
                    createdAt: $record['created_at'],
                    customerId: $record['customer_id']
                ))
        );
    }
}
