<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

use TinyBlocks\Currency\Currency;

final class Amount
{
    public function __construct(public float $value, public Currency $currency)
    {
    }
}
