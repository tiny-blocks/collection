<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use TinyBlocks\Serializer\SerializeKeys;

enum PreserveKeys: int
{
    case DISCARD = 0;
    case PRESERVE = 1;

    public function toSerializeKeys(): SerializeKeys
    {
        return $this === self::PRESERVE ? SerializeKeys::PRESERVE : SerializeKeys::DISCARD;
    }
}
