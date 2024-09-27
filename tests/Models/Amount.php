<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

final class Amount
{
    public function __construct(public float $value, public Currency $currency)
    {
    }
}
