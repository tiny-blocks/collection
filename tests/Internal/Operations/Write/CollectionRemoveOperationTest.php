<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Operations\Write;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use Test\TinyBlocks\Collection\Models\Dragon;
use Test\TinyBlocks\Collection\Models\Status;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Currency\Currency;

final class CollectionRemoveOperationTest extends TestCase
{
    public function testRemoveAllElements(): void
    {
        /** @Given a Bitcoin (BTC) */
        $bitcoin = new CryptoCurrency(name: 'Bitcoin', price: (float)rand(60000, 999999), symbol: 'BTC');

        /** @And an Ethereum (ETH) */
        $ethereum = new CryptoCurrency(name: 'Ethereum', price: (float)rand(10000, 60000), symbol: 'ETH');

        /** @And a collection containing these elements */
        $collection = Collection::createFrom(elements: [$bitcoin, $ethereum]);

        /** @When removing all elements without a callback */
        $actual = $collection->removeAll();

        /** @Then the collection should be empty */
        self::assertEmpty($actual->toArray());
    }

    #[DataProvider('elementRemovalDataProvider')]
    public function testRemoveSpecificElement(mixed $element, iterable $elements, iterable $expected): void
    {
        /** @Given a collection created from initial elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When removing the specified element */
        $actual = $collection->remove(element: $element);

        /** @Then the collection should no longer contain the removed element */
        self::assertSame($expected, $actual->toArray());
    }

    public function testRemoveSpecificElementUsingFilter(): void
    {
        /** @Given a Bitcoin (BTC) */
        $bitcoin = new CryptoCurrency(name: 'Bitcoin', price: (float)rand(60000, 999999), symbol: 'BTC');

        /** @And an Ethereum (ETH) */
        $ethereum = new CryptoCurrency(name: 'Ethereum', price: (float)rand(10000, 60000), symbol: 'ETH');

        /** @And a collection containing these elements */
        $collection = Collection::createFrom(elements: [$bitcoin, $ethereum]);

        /** @When removing the Bitcoin (BTC) element using a filter */
        $actual = $collection->removeAll(filter: static fn(CryptoCurrency $item) => $item === $bitcoin);

        /** @Then the collection should no longer contain the removed element */
        self::assertSame([$ethereum->toArray()], $actual->toArray());
    }

    public static function elementRemovalDataProvider(): iterable
    {
        $dragonOne = new Dragon(name: 'Udron', description: 'The taker of life.');
        $dragonTwo = new Dragon(name: 'Ignarion', description: 'Majestic guardian of fiery realms.');

        $bitcoin = new CryptoCurrency(name: 'Bitcoin', price: (float)rand(60000, 999999), symbol: 'BTC');
        $ethereum = new CryptoCurrency(name: 'Ethereum', price: (float)rand(10000, 60000), symbol: 'ETH');

        $spTimeZone = new DateTimeZone('America/Sao_Paulo');
        $dateOne = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '1997-01-01 00:00:00', $spTimeZone);
        $dateTwo = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '1997-01-02 00:00:00', $spTimeZone);

        yield 'Remove enum from collection' => [
            'element'  => Currency::BRL,
            'elements' => [Currency::BRL, Status::PAID, Currency::USD, Status::PENDING],
            'expected' => [Status::PAID->value, Currency::USD->name, Status::PENDING->value]
        ];

        yield 'Remove null from collection' => [
            'element'  => null,
            'elements' => [$ethereum, null, $bitcoin],
            'expected' => [$ethereum->toArray(), $bitcoin->toArray()]
        ];

        yield 'Remove date from collection' => [
            'element'  => $dateOne,
            'elements' => [$dateOne, $dateTwo],
            'expected' => ['1997-01-02T00:00:00-02:00']
        ];

        yield 'Remove dragon from collection' => [
            'element'  => $dragonOne,
            'elements' => [$dragonOne, $dragonTwo],
            'expected' => [
                ['name' => $dragonTwo->name, 'description' => $dragonTwo->description]
            ]
        ];

        yield 'Remove scalar values from collection' => [
            'element'  => 50,
            'elements' => [true, 100, 'xpto', 50, 1000.0001, null, ['id' => 1]],
            'expected' => [true, 100, 'xpto', 1000.0001, null, ['id' => 1]]
        ];

        yield 'Remove crypto currency from collection' => [
            'element'  => $bitcoin,
            'elements' => [$ethereum, null, $bitcoin],
            'expected' => [$ethereum->toArray(), null]
        ];

        yield 'Remove list of elements from array iterators' => [
            'element'  => [4, 5, 6],
            'elements' => new ArrayIterator([[1, 2, 3], [4, 5, 6], [7, 8, 9]]),
            'expected' => [[1, 2, 3], [7, 8, 9]]
        ];
    }
}
