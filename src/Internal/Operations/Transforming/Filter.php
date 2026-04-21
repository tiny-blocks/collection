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

        $this->compiledPredicate = $filtered === []
            ? static fn(mixed $value, mixed $key): bool => (bool)$value
            : static fn(mixed $value, mixed $key): bool => array_all(
                $filtered,
                static fn(Closure $predicate): bool => $predicate($value, $key)
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
