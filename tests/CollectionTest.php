<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Order;
use TinyBlocks\Currency\Currency;

final class CollectionTest extends TestCase
{
    public function testChainedOperationsWithObjects(): void
    {
        /** @Given a collection of Amount objects */
        $collection = Collection::createFrom(elements: [
            new Amount(value: 50.00, currency: Currency::USD),
            new Amount(value: 100.00, currency: Currency::USD),
            new Amount(value: 150.00, currency: Currency::USD),
            new Amount(value: 250.00, currency: Currency::USD),
            new Amount(value: 500.00, currency: Currency::USD)
        ]);

        /** @When chaining multiple operations:
         *  filter amounts greater than or equal to 100,
         *  apply a 10% discount,
         *  remove amounts greater than 300 after the discount,
         *  sort amounts in ascending order,
         *  and use each to accumulate the total discounted value */
        $totalDiscounted = 0;
        $actual = $collection
            ->filter(predicates: static fn(Amount $amount): bool => $amount->value >= 100)
            ->map(transformations: static fn(Amount $amount): Amount => new Amount(
                value: $amount->value * 0.9,
                currency: $amount->currency
            ))
            ->removeAll(filter: static fn(Amount $amount): bool => $amount->value > 300)
            ->sort(order: Order::ASCENDING_VALUE, predicate: static fn(
                Amount $first,
                Amount $second
            ): int => $first->value <=> $second->value)
            ->each(actions: function (Amount $amount) use (&$totalDiscounted) {
                $totalDiscounted += $amount->value;
            });

        /** @Then the final collection should contain exactly three elements */
        self::assertCount(3, $actual);

        /** @And the total discounted value should be calculated correctly */
        self::assertSame(450.00, $totalDiscounted);

        /** @And the first Amount should be 90 after the discount */
        self::assertSame(90.00, $actual->first()->value);

        /** @And the last Amount should be 225 after the discount */
        self::assertSame(225.00, $actual->last()->value);
    }

    public function testChainedOperationsWithIntegers(): void
    {
        /** @Given a collection of integers from 1 to 100 */
        $collection = Collection::createFrom(elements: range(1, 100));

        /** @When filtering even numbers,
         *  Then mapping each number to its square,
         *  And sorting the squared numbers in descending order */
        $actual = $collection
            ->filter(predicates: static fn(int $value): bool => $value % 2 === 0)
            ->map(transformations: static fn(int $value): int => $value ** 2)
            ->sort(order: Order::DESCENDING_VALUE);

        /** @Then the first element after sorting should be 10,000 (square of 100) */
        self::assertSame(10000, $actual->first());

        /** @And the last element after sorting should be four (square of 2) */
        self::assertSame(4, $actual->last());

        /** @When reducing the collection to calculate the sum of all squared even numbers */
        $sum = $actual->reduce(aggregator: static fn(int $carry, int $value): int => $carry + $value, initial: 0);

        /** @Then the sum of squared even numbers should be correct (171700) */
        self::assertSame(171700, $sum);
    }
}
