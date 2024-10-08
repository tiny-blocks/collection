<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Transform;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\Currency;

final class CollectionGroupByOperationTest extends TestCase
{
    public function testGroupByObjects(): void
    {
        /** @Given a collection of Amount objects with different currencies */
        $collection = Collection::createFrom(elements: [
            new Amount(value: 55.1, currency: Currency::BRL),
            new Amount(value: 100.5, currency: Currency::USD),
            new Amount(value: 23.3, currency: Currency::BRL),
            new Amount(value: 200.0, currency: Currency::USD)
        ]);

        /** @When grouping by the currency property */
        $actual = $collection->groupBy(grouping: static fn(Amount $amount): string => $amount->currency->name);

        /** @Then the collection should be grouped by the currency */
        $expected = Collection::createFrom(elements: [
            'BRL' => [
                new Amount(value: 55.1, currency: Currency::BRL),
                new Amount(value: 23.3, currency: Currency::BRL)
            ],
            'USD' => [
                new Amount(value: 100.5, currency: Currency::USD),
                new Amount(value: 200.0, currency: Currency::USD)
            ]
        ]);

        self::assertEquals($expected->toArray(), $actual->toArray());
    }

    public function testGroupBySimpleKey(): void
    {
        /** @Given a collection of elements with a type property */
        $collection = Collection::createFrom(elements: [
            ['type' => 'fruit', 'name' => 'apple'],
            ['type' => 'fruit', 'name' => 'banana'],
            ['type' => 'vegetable', 'name' => 'carrot'],
            ['type' => 'vegetable', 'name' => 'broccoli']
        ]);

        /** @When grouping by the 'type' key */
        $actual = $collection->groupBy(grouping: static fn(array $item): string => $item['type']);

        /** @Then the collection should be grouped by the type property */
        $expected = Collection::createFrom(elements: [
            'fruit'     => Collection::createFrom(elements: [
                ['type' => 'fruit', 'name' => 'apple'],
                ['type' => 'fruit', 'name' => 'banana']
            ]),
            'vegetable' => Collection::createFrom(elements: [
                ['type' => 'vegetable', 'name' => 'carrot'],
                ['type' => 'vegetable', 'name' => 'broccoli']
            ])
        ]);

        self::assertSame($expected->toArray(), $actual->toArray());
    }

    public function testGroupByNumericKey(): void
    {
        /** @Given a collection of numbers */
        $collection = Collection::createFrom(elements: [1, 2, 3, 4, 5, 6]);

        /** @When grouping by even and odd numbers */
        $actual = $collection->groupBy(grouping: static fn(int $item): string => $item % 2 === 0 ? 'even' : 'odd');

        /** @Then the collection should be grouped into even and odd */
        $expected = Collection::createFrom(elements: [
            'odd'  => Collection::createFrom(elements: [1, 3, 5]),
            'even' => Collection::createFrom(elements: [2, 4, 6])
        ]);

        self::assertSame($expected->toArray(), $actual->toArray());
    }

    public function testGroupByEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @When applying groupBy on the empty collection */
        $actual = $collection->groupBy(grouping: static fn(array $item): array => $item);

        /** @Then the collection should remain empty */
        self::assertEmpty($actual->toArray());
    }
}
