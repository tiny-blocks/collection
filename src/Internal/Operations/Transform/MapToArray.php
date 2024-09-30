<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;
use TinyBlocks\Serializer\IterableSerializer;

final readonly class MapToArray implements ImmediateOperation
{
    private IterableSerializer $serializer;

    private function __construct(iterable $elements, private PreserveKeys $preserveKeys)
    {
        $this->serializer = new IterableSerializer(iterable: $elements);
    }

    public static function from(iterable $elements, PreserveKeys $preserveKeys): MapToArray
    {
        return new MapToArray(elements: $elements, preserveKeys: $preserveKeys);
    }

    public function toArray(): array
    {
        return $this->serializer->toArray(serializeKeys: $this->preserveKeys->toSerializeKeys());
    }
}
