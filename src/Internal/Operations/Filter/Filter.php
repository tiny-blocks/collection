<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Filter;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final readonly class Filter implements LazyOperation
{
    private array $predicates;

    private Closure $compiledPredicate;

    private function __construct(?Closure ...$predicates)
    {
        $this->predicates = array_filter($predicates);

        $buildCompositePredicate = static fn(array $predicates): Closure => static fn(
            mixed $value,
            mixed $key
        ): bool => array_all(
            $predicates,
            static fn(Closure $predicate): bool => $predicate($value, $key)
        );

        $this->compiledPredicate = match (count($this->predicates)) {
            0 => static fn(mixed $value, mixed $key): bool => (bool)$value,
            default => $buildCompositePredicate($this->predicates)
        };
    }

    public static function from(?Closure ...$predicates): Filter
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
