<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Generator;
use SplQueue;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class Segment implements Operation
{
    private function __construct(private int $offset, private int $length)
    {
    }

    public static function from(int $offset, int $length): Segment
    {
        return new Segment(offset: $offset, length: $length);
    }

    public function apply(iterable $elements): Generator
    {
        if ($this->length === 0) {
            return;
        }

        yield from $this->length < -1
            ? $this->dropFromEnd(elements: $elements)
            : $this->takeInOrder(elements: $elements);
    }

    private function hasReached(int $emitted): bool
    {
        return $this->length !== -1 && $emitted >= $this->length;
    }

    private function dropFromEnd(iterable $elements): Generator
    {
        $buffer = new SplQueue();
        $skipFromEnd = abs($this->length);
        $currentIndex = 0;

        foreach ($elements as $key => $value) {
            if ($currentIndex++ < $this->offset) {
                continue;
            }

            $buffer->enqueue([$key, $value]);

            if ($buffer->count() > $skipFromEnd) {
                [$yieldKey, $yieldValue] = $buffer->dequeue();
                yield $yieldKey => $yieldValue;
            }
        }
    }

    private function takeInOrder(iterable $elements): Generator
    {
        $currentIndex = 0;
        $emitted = 0;

        foreach ($elements as $key => $value) {
            if ($currentIndex++ < $this->offset) {
                continue;
            }

            yield $key => $value;
            $emitted++;

            if ($this->hasReached(emitted: $emitted)) {
                return;
            }
        }
    }
}
