<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

final readonly class Order
{
    public function __construct(public int $id, public Products $products)
    {
    }
}
