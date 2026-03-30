<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class GroupInto implements Operation
{
    private function __construct(private Closure $classifier)
    {
    }

    public static function by(Closure $classifier): GroupInto
    {
        return new GroupInto(classifier: $classifier);
    }

    public function apply(iterable $elements): Generator
    {
        $groups = [];

        foreach ($elements as $element) {
            $key = ($this->classifier)($element);
            $groups[$key][] = $element;
        }

        yield from $groups;
    }
}
