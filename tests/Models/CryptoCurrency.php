<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final class CryptoCurrency implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(public string $name, public float $price, public string $symbol)
    {
    }
}
