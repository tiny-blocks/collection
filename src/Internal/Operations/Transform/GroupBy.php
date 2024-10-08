<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final readonly class GroupBy implements LazyOperation
{
    private function __construct(private Closure $grouping)
    {
    }

    public static function from(Closure $grouping): GroupBy
    {
        return new GroupBy(grouping: $grouping);
    }

    public function apply(iterable $elements): Generator
    {
        $groupedElements = [];

        foreach ($elements as $element) {
            $key = ($this->grouping)($element);
            $groupedElements[$key][] = $element;
        }

        foreach ($groupedElements as $key => $group) {
            yield $key => $group;
        }
    }
}
