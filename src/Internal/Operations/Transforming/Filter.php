<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class Filter implements Operation
{
    private Closure $compiledPredicate;

    private function __construct(?Closure ...$predicates)
    {
        $filtered = array_filter($predicates, static fn(?Closure $predicate): bool => !is_null($predicate));

        if ($filtered === []) {
            $this->compiledPredicate = static fn(mixed $value, mixed $key): bool => (bool)$value;

            return;
        }

        $first = array_shift($filtered);

        $this->compiledPredicate = array_reduce(
            $filtered,
            static fn(Closure $carry, Closure $predicate): Closure
                => static fn(mixed $value, mixed $key): bool => $carry($value, $key) && $predicate($value, $key),
            $first
        );
    }

    public static function matching(?Closure ...$predicates): Filter
    {
        return new Filter(...$predicates);
    }

    public function apply(iterable $elements): Generator
    {
        $predicate = $this->compiledPredicate;

        foreach ($elements as $key => $value) {
            if ($predicate($value, $key)) {
                yield $key => $value;
            }
        }
    }
}
