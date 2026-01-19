<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Operations\Write;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\Amount;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use Test\TinyBlocks\Collection\Models\Dragon;
use Test\TinyBlocks\Collection\Models\Order;
use Test\TinyBlocks\Collection\Models\Product;
use Test\TinyBlocks\Collection\Models\Products;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Currency\Currency;


final class CollectionAddOperationTest extends TestCase
{
    #[DataProvider('elementsAndAdditionsDataProvider')]
    public function testAddElementsToCollection(
        iterable $fromElements,
        iterable $addElements,
        iterable $expected
    ): void {
        /** @Given a collection created from initial elements */
        $collection = Collection::createFrom(elements: $fromElements);

        /** @When adding elements to the collection using the add method */
        $collection->add(...$addElements);

        /** @Then the collection should contain the expected elements */
        self::assertSame($expected, $collection->toArray());
    }

    public static function elementsAndAdditionsDataProvider(): iterable
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

        yield 'Add null value' => [
            'fromElements' => [],
            'addElements'  => [null],
            'expected'     => [null]
        ];

        yield 'Add single array' => [
            'fromElements' => [],
            'addElements'  => [[1, 2, 3]],
            'expected'     => [[1, 2, 3]]
        ];

        yield 'Add single float' => [
            'fromElements' => [],
            'addElements'  => [3.14],
            'expected'     => [3.14]
        ];

        yield 'Add single string' => [
            'fromElements' => [],
            'addElements'  => ['test'],
            'expected'     => ['test']
        ];

        yield 'Add orders objects' => [
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

        yield 'Add dragon objects' => [
            'fromElements' => [$dragonOne],
            'addElements'  => [$dragonTwo, $dragonThree],
            'expected'     => [
                ['name' => $dragonOne->name, 'description' => $dragonOne->description],
                ['name' => $dragonTwo->name, 'description' => $dragonTwo->description],
                ['name' => $dragonThree->name, 'description' => $dragonThree->description]
            ]
        ];

        yield 'Add boolean values' => [
            'fromElements' => [],
            'addElements'  => [true, false],
            'expected'     => [true, false]
        ];

        yield 'Add single integer' => [
            'fromElements' => [],
            'addElements'  => [42],
            'expected'     => [42]
        ];

        yield 'Add crypto currency objects' => [
            'fromElements' => [$bitcoin],
            'addElements'  => [$ethereum],
            'expected'     => [
                ['name' => $bitcoin->name, 'price' => $bitcoin->price, 'symbol' => $bitcoin->symbol],
                ['name' => $ethereum->name, 'price' => $ethereum->price, 'symbol' => $ethereum->symbol]
            ]
        ];

        yield 'Add mixed types of primitives' => [
            'fromElements' => [],
            'addElements'  => [42, 'test', 3.14, null, [1, 2, 3]],
            'expected'     => [42, 'test', 3.14, null, [1, 2, 3]]
        ];
    }
}
