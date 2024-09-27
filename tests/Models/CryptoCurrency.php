<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

use TinyBlocks\Serializer\Serializer;
use TinyBlocks\Serializer\SerializerAdapter;
use TinyBlocks\Vo\ValueObject;
use TinyBlocks\Vo\ValueObjectAdapter;

final class CryptoCurrency implements Serializer, ValueObject
{
    use SerializerAdapter;
    use ValueObjectAdapter;

    public function __construct(public string $name, public float $price, public string $symbol)
    {
    }
}
