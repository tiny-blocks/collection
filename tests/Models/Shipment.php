<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

final readonly class Shipment
{
    private function __construct(
        public string $id,
        public string $status,
        public string $carrier,
        public string $createdAt,
        public string $customerId
    ) {
    }

    public static function from(
        string $id,
        string $status,
        string $carrier,
        string $createdAt,
        string $customerId
    ): Shipment {
        return new Shipment(
            id: $id,
            status: $status,
            carrier: $carrier,
            createdAt: $createdAt,
            customerId: $customerId
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'carrier'     => $this->carrier,
            'created_at'  => $this->createdAt,
            'customer_id' => $this->customerId
        ];
    }
}
