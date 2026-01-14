<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Retrieve;

use Generator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final readonly class Slice implements LazyOperation
{
    private function __construct(private int $index, private int $length)
    {
    }

    public static function from(int $index, int $length): Slice
    {
        return new Slice(index: $index, length: $length);
    }

    public function apply(iterable $elements): Generator
    {
        if ($this->length === 0) {
            return;
        }

        if ($this->length < -1) {
            yield from $this->applyWithBufferedSlice(elements: $elements);
            return;
        }

        $currentIndex = 0;
        $yieldedCount = 0;

        foreach ($elements as $key => $value) {
            if ($currentIndex++ < $this->index) {
                continue;
            }

            yield $key => $value;
            $yieldedCount++;

            if ($this->length !== -1 && $yieldedCount >= $this->length) {
                return;
            }
        }
    }

    private function applyWithBufferedSlice(iterable $elements): Generator
    {
        $collected = [];
        $currentIndex = 0;

        foreach ($elements as $key => $value) {
            if ($currentIndex++ < $this->index) {
                continue;
            }

            $collected[] = [$key, $value];
        }

        $collected = array_slice($collected, 0, $this->length);

        foreach ($collected as [$key, $value]) {
            yield $key => $value;
        }
    }
}
