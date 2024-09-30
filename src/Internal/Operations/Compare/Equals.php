<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Compare;

use TinyBlocks\Collection\Collectible;
use TinyBlocks\Collection\Internal\Iterators\IterableIteratorAggregate;
use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class Equals implements ImmediateOperation
{
    private function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): Equals
    {
        return new Equals(elements: $elements);
    }

    public static function build(): Equals
    {
        return new Equals(elements: []);
    }

    public function compareAll(Collectible $other): bool
    {
        $currentIterator = (new IterableIteratorAggregate(elements: $other))->getIterator();
        $targetIterator = (new IterableIteratorAggregate(elements: $this->elements))->getIterator();

        while ($currentIterator->valid() || $targetIterator->valid()) {
            if (!$currentIterator->valid() || !$targetIterator->valid()) {
                return false;
            }

            if (!$this->compareWith(element: $currentIterator->current(), otherElement: $targetIterator->current())) {
                return false;
            }

            $currentIterator->next();
            $targetIterator->next();
        }

        return true;
    }

    public function compareWith(mixed $element, mixed $otherElement): bool
    {
        return is_object($element) && is_object($otherElement)
            ? $element == $otherElement
            : $element === $otherElement;
    }
}
