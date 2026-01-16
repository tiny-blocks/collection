<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Operations\Compare;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Collection;

final class CollectionContainsOperationTest extends TestCase
{
    #[DataProvider('containsElementDataProvider')]
    public function testContainsElement(iterable $elements, mixed $element): void
    {
        /** @Given a collection */
        $collection = Collection::createFrom(elements: $elements);

        /** @When checking if the element is contained in the collection */
        $actual = $collection->contains(element: $element);

        /** @Then the collection should contain the element */
        self::assertTrue($actual);
    }

    #[DataProvider('doesNotContainElementDataProvider')]
    public function testDoesNotContainElement(iterable $elements, mixed $element): void
    {
        /** @Given a collection */
        $collection = Collection::createFrom(elements: $elements);

        /** @When checking if the element is contained in the collection */
        $actual = $collection->contains(element: $element);

        /** @Then the collection should not contain the element */
        self::assertFalse($actual);
    }

    public static function containsElementDataProvider(): iterable
    {
        yield 'Collection contains null' => [
            'elements' => [1, null, 3],
            'element'  => null
        ];

        yield 'Collection contains element' => [
            'elements' => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
            ],
            'element'  => new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC')
        ];

        yield 'Collection contains scalar value' => [
            'elements' => [1, 'key' => 'value', 3.5],
            'element'  => 'value'
        ];
    }

    public static function doesNotContainElementDataProvider(): iterable
    {
        yield 'Empty collection' => [
            'elements' => [],
            'element'  => 1
        ];

        yield 'Collection does not contain object' => [
            'elements' => [new stdClass()],
            'element'  => new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC')
        ];

        yield 'Collection does not contain element' => [
            'elements' => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
            ],
            'element'  => new CryptoCurrency(name: 'Ripple', price: 1.0, symbol: 'XRP')
        ];

        yield 'Collection does not contain scalar value' => [
            'elements' => [1, 'key' => 'value', 3.5],
            'element'  => 42
        ];
    }
}
