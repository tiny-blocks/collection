<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use TinyBlocks\Serializer\SerializeKeys;

/**
 * Defines how array keys should be handled when converting collections.
 */
enum PreserveKeys: int
{
    /**
     * Discards the array keys during serialization.
     */
    case DISCARD = 0;

    /**
     * Preserves the array keys during serialization.
     */
    case PRESERVE = 1;

    public function toSerializeKeys(): SerializeKeys
    {
        return $this === self::PRESERVE ? SerializeKeys::PRESERVE : SerializeKeys::DISCARD;
    }
}
