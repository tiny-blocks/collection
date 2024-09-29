<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Filter;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;

final class Filter implements ApplicableOperation
{
    private array $predicates;

    private function __construct(Closure ...$predicates)
    {
        $this->predicates = $predicates;
    }

    public static function from(Closure ...$predicates): Filter
    {
        return new Filter(...$predicates);
    }

    public function apply(iterable $elements): Generator
    {
        $predicate = $this->predicates
            ? function (mixed $value, mixed $key): bool {
                return array_reduce(
                    $this->predicates,
                    static fn(bool $isValid, Closure $predicate): bool => $isValid && $predicate($value, $key),
                    true
                );
            }
            : static fn(mixed $value): bool => (bool)$value;

        foreach ($elements as $key => $value) {
            if ($predicate($value, $key)) {
                yield $key => $value;
            }
        }
    }
}
