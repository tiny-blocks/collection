<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Write;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Models\Currency;
use TinyBlocks\Collection\Models\Dragon;
use TinyBlocks\Collection\Models\Order;
use TinyBlocks\Collection\Models\Product;
use TinyBlocks\Collection\Models\Products;

final class CollectionAddOperationTest extends TestCase
{
    #[DataProvider('fromElementsAndAdditionsDataProvider')]
    public function testFromAndAddPrimitiveElementsToCollection(
        iterable $fromElements,
        iterable $addElements,
        iterable $expected
    ): void {
        /** @Given a collection created from initial elements */
        $collection = Collection::from(elements: $fromElements);

        /** @When adding elements to the collection using the add method */
        $collection->add(...$addElements);

        /** @Then the collection should contain the expected elements */
        self::assertSame($expected, $collection->toArray());
    }

    #[DataProvider('fromElementsAndAddObjectsDataProvider')]
    public function testFromAndAddObjectsToCollection(
        iterable $fromElements,
        iterable $addElements,
        iterable $expected
    ): void {
        /** @Given a collection created from initial elements */
        $collection = Collection::from(elements: $fromElements);

        /** @When adding elements to the collection using the add method */
        $collection->add(...$addElements);

        /** @Then the collection should contain the expected elements */
        self::assertSame($expected, $collection->toArray());
    }

    #[DataProvider('primitiveElementsDataProvider')]
    public function testAddPrimitiveElementsToCollection(iterable $elements, iterable $expected): void
    {
        /** @Given an empty collection */
        $collection = Collection::fromEmpty();

        /** @When adding elements to the collection using add */
        $collection->add(...$elements);

        /** @Then the collection should contain the expected elements */
        self::assertSame($expected, iterator_to_array($collection));
    }

    public static function primitiveElementsDataProvider(): iterable
    {
        yield 'Add single float' => [
            'elements' => [3.14],
            'expected' => [3.14]
        ];

        yield 'Add single string' => [
            'elements' => ['test'],
            'expected' => ['test']
        ];

        yield 'Add boolean values' => [
            'elements' => [true, false],
            'expected' => [true, false]
        ];

        yield 'Add single integer' => [
            'elements' => [42],
            'expected' => [42]
        ];

        yield 'Add single array' => [
            'elements' => [[1, 2, 3]],
            'expected' => [[1, 2, 3]]
        ];

        yield 'Add null value' => [
            'elements' => [null],
            'expected' => [null]
        ];

        yield 'Add mixed types of primitives' => [
            'elements' => [42, 'test', 3.14, null, [1, 2, 3]],
            'expected' => [42, 'test', 3.14, null, [1, 2, 3]]
        ];
    }

    public static function fromElementsAndAdditionsDataProvider(): iterable
    {
        yield 'From with array and add null' => [
            'fromElements' => [[1, 2, 3]],
            'addElements'  => [null],
            'expected'     => [[1, 2, 3], null]
        ];

        yield 'From with integer and add boolean' => [
            'fromElements' => [42],
            'addElements'  => [true],
            'expected'     => [42, true]
        ];

        yield 'From with mixed types and add more' => [
            'fromElements' => [1, 'hello'],
            'addElements'  => [2.5, false],
            'expected'     => [1, 'hello', 2.5, false]
        ];

        yield 'From with single float and add string' => [
            'fromElements' => [3.14],
            'addElements'  => ['test'],
            'expected'     => [3.14, 'test']
        ];
    }

    public static function fromElementsAndAddObjectsDataProvider(): iterable
    {
        $dragonOne = new Dragon(name: 'Udron', description: 'The taker of life.');
        $dragonTwo = new Dragon(name: 'Ignarion', description: 'Majestic guardian of fiery realms.');
        $dragonThree = new Dragon(name: 'Ignivar Bloodwing', description: 'Fierce guardian of volcanic mountains.');

        $bitcoin = new CryptoCurrency(name: 'Bitcoin', price: (float)rand(60000, 999999), symbol: 'BTC');
        $ethereum = new CryptoCurrency(name: 'Ethereum', price: (float)rand(10000, 60000), symbol: 'ETH');

        $productOne = new Product(name: 'Product One', amount: new Amount(value: 100.50, currency: Currency::USD));
        $productTwo = new Product(name: 'Product Two', amount: new Amount(value: 200.75, currency: Currency::BRL));

        $orderOne = new Order(id: 1, products: new Products(elements: [$productOne, $productTwo]));
        $orderTwo = new Order(id: 2, products: new Products(elements: [$productOne]));

        yield 'Add Orders objects' => [
            'fromElements' => [$orderOne],
            'addElements'  => [$orderTwo],
            'expected'     => [
                [
                    'id'       => 1,
                    'products' => [$productOne->toArray(), $productTwo->toArray()]
                ],
                [
                    'id'       => 2,
                    'products' => [$productOne->toArray()]
                ]
            ]
        ];

        yield 'Add Dragon objects' => [
            'fromElements' => [$dragonOne],
            'addElements'  => [$dragonTwo, $dragonThree],
            'expected'     => [
                ['name' => $dragonOne->name, 'description' => $dragonOne->description],
                ['name' => $dragonTwo->name, 'description' => $dragonTwo->description],
                ['name' => $dragonThree->name, 'description' => $dragonThree->description]
            ]
        ];

        yield 'Add CryptoCurrency objects' => [
            'fromElements' => [$bitcoin],
            'addElements'  => [$ethereum],
            'expected'     => [
                ['name' => $bitcoin->name, 'price' => $bitcoin->price, 'symbol' => $bitcoin->symbol],
                ['name' => $ethereum->name, 'price' => $ethereum->price, 'symbol' => $ethereum->symbol]
            ]
        ];
    }
}
