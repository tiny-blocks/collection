<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;
use TinyBlocks\Collection\PreserveKeys;
use TinyBlocks\Serializer\IterableSerializer;

final readonly class MapToJson implements ImmediateOperation
{
    private IterableSerializer $serializer;

    private function __construct(iterable $elements, private PreserveKeys $preserveKeys)
    {
        $this->serializer = new IterableSerializer(iterable: $elements);
    }

    public static function from(iterable $elements, PreserveKeys $preserveKeys): MapToJson
    {
        return new MapToJson(elements: $elements, preserveKeys: $preserveKeys);
    }

    public function toJson(): string
    {
        return $this->serializer->toJson(serializeKeys: $this->preserveKeys->toSerializeKeys());
    }
}
