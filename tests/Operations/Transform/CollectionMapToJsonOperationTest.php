<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Transform;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\Currency;
use TinyBlocks\Collection\Models\Status;

final class CollectionMapToJsonOperationTest extends TestCase
{
    #[DataProvider('elementsDataProvider')]
    public function testCollectionToJsonConversion(iterable $elements, string $expected): void
    {
        /** @Given a collection with elements */
        $collection = Collection::from(elements: $elements);

        /** @When converting the collection to JSON */
        $actual = $collection->toJson();

        /** @Then the JSON representation should match the expected format */
        self::assertSame($expected, $actual);
    }

    public static function elementsDataProvider(): iterable
    {
        $amountInBrl = new Amount(value: 55.1, currency: Currency::BRL);
        $amountInUsd = new Amount(value: 55.2, currency: Currency::USD);

        yield 'Convert unit enums to JSON' => [
            'elements' => [Currency::USD, Currency::BRL],
            'expected' => '["USD","BRL"]'
        ];

        yield 'Convert mixed types to JSON' => [
            'elements' => ['iPhone', 42, true, $amountInUsd],
            'expected' => '["iPhone",42,true,{"value":55.2,"currency":"USD"}]'
        ];

        yield 'Convert backed enums to JSON' => [
            'elements' => [Status::PAID, Status::PENDING],
            'expected' => '[1,0]'
        ];

        yield 'Convert nested arrays to JSON' => [
            'elements' => [
                ['name' => 'Item 1', 'details' => ['price' => 100, 'stock' => 10]],
                ['name' => 'Item 2', 'details' => ['price' => 200, 'stock' => 5]]
            ],
            'expected' => '[{"name":"Item 1","details":{"price":100,"stock":10}},{"name":"Item 2","details":{"price":200,"stock":5}}]'
        ];

        yield 'Convert boolean values to JSON' => [
            'elements' => [true, false],
            'expected' => '[true,false]'
        ];

        yield 'Convert empty collection to JSON' => [
            'elements' => [],
            'expected' => '[]'
        ];

        yield 'Convert array of strings to JSON' => [
            'elements' => ['iPhone', 'iPad', 'MacBook'],
            'expected' => '["iPhone","iPad","MacBook"]'
        ];

        yield 'Convert array of integers to JSON' => [
            'elements' => [1, 2, 3],
            'expected' => '[1,2,3]'
        ];

        yield 'Convert array of amount objects to JSON' => [
            'elements' => [$amountInBrl, $amountInUsd],
            'expected' => '[{"value":55.1,"currency":"BRL"},{"value":55.2,"currency":"USD"}]'
        ];
    }
}
