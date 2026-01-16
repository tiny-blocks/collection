<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Operations\Transform;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\Amount;
use Test\TinyBlocks\Collection\Models\Status;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Currency\Currency;

final class CollectionMapToArrayOperationTest extends TestCase
{
    #[DataProvider('elementsDataProvider')]
    public function testCollectionToArrayConversion(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When converting the collection to array */
        $actual = $collection->toArray();

        /** @Then the array representation should match the expected format */
        self::assertSame($expected, $actual);
        self::assertSame(count((array)$expected), $collection->count());
    }

    public static function elementsDataProvider(): iterable
    {
        $amountInBrl = new Amount(value: 55.1, currency: Currency::BRL);
        $amountInUsd = new Amount(value: 55.2, currency: Currency::USD);

        yield 'Convert unit enums to array' => [
            'elements' => [Currency::USD, Currency::BRL],
            'expected' => [Currency::USD->name, Currency::BRL->name]
        ];

        yield 'Convert mixed types to array' => [
            'elements' => ['iPhone', 42, true, $amountInUsd],
            'expected' => [
                'iPhone',
                42,
                true,
                ['value' => $amountInUsd->value, 'currency' => $amountInUsd->currency->name]
            ]
        ];

        yield 'Convert backed enums to array' => [
            'elements' => [Status::PAID, Status::PENDING],
            'expected' => [Status::PAID->value, Status::PENDING->value]
        ];

        yield 'Convert nested arrays to array' => [
            'elements' => [
                ['name' => 'Item 1', 'details' => ['price' => 100, 'stock' => 10]],
                ['name' => 'Item 2', 'details' => ['price' => 200, 'stock' => 5]]
            ],
            'expected' => [
                ['name' => 'Item 1', 'details' => ['price' => 100, 'stock' => 10]],
                ['name' => 'Item 2', 'details' => ['price' => 200, 'stock' => 5]]
            ]
        ];

        yield 'Convert boolean values to array' => [
            'elements' => [true, false],
            'expected' => [true, false]
        ];

        yield 'Convert empty collection to array' => [
            'elements' => [],
            'expected' => []
        ];

        yield 'Convert array of strings to array' => [
            'elements' => ['iPhone', 'iPad', 'MacBook'],
            'expected' => ['iPhone', 'iPad', 'MacBook']
        ];

        yield 'Convert array of integers to array' => [
            'elements' => [1, 2, 3],
            'expected' => [1, 2, 3]
        ];

        yield 'Convert array of amount objects to array' => [
            'elements' => [$amountInBrl, $amountInUsd],
            'expected' => [
                ['value' => $amountInBrl->value, 'currency' => $amountInBrl->currency->name],
                ['value' => $amountInUsd->value, 'currency' => $amountInUsd->currency->name]
            ]
        ];
    }
}
