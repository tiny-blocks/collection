<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Filter;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final readonly class Filter implements LazyOperation
{
    private array $predicates;

    private function __construct(?Closure ...$predicates)
    {
        $this->predicates = array_filter($predicates);
    }

    public static function from(?Closure ...$predicates): Filter
    {
        return new Filter(...$predicates);
    }

    public function apply(iterable $elements): Generator
    {
        $predicate = $this->predicates
            ? function (mixed $value, mixed $key): bool {
                foreach ($this->predicates as $predicate) {
                    if (!$predicate($value, $key)) {
                        return false;
                    }
                }
                return true;
            }
            : static fn(mixed $value): bool => (bool)$value;

        foreach ($elements as $key => $value) {
            if ($predicate($value, $key)) {
                yield $key => $value;
            }
        }
    }
}
