<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Aggregate;

use Closure;
use TinyBlocks\Collection\Internal\Operations\NonApplicableOperation;

final readonly class Reduce implements NonApplicableOperation
{
    public function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): Reduce
    {
        return new Reduce(elements: $elements);
    }

    public function execute(Closure $aggregator, mixed $initial): mixed
    {
        $carry = $initial;

        foreach ($this->elements as $element) {
            $carry = $aggregator($carry, $element);
        }

        return $carry;
    }
}
