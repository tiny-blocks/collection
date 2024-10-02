<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

use TinyBlocks\Serializer\Serializer;
use TinyBlocks\Serializer\SerializerAdapter;

final class CryptoCurrency implements Serializer
{
    use SerializerAdapter;

    public function __construct(public string $name, public float $price, public string $symbol)
    {
    }
}
