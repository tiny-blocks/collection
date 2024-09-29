<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Internal\Operations\Order\Order;
use TinyBlocks\Collection\Internal\Operations\Transform\PreserveKeys;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\Currency;

final class CollectionTest extends TestCase
{
    public function testAddMapSortAndToJson(): void
    {
        /** @Given a collection */
        $collection = Collection::createFrom(elements: [8, 3, 7]);

        /** @When adding, mapping to Amount objects, sorting by value, and converting to JSON */
        $collection
            ->add(elements: 4)
            ->map(transformations: fn(int $value): Amount => new Amount(value: $value, currency: Currency::USD))
            ->sort(
                order: Order::ASCENDING_VALUE,
                predicate: fn(Amount $first, Amount $second): int => $first->value <=> $second->value
            );

        /** @Then asserting the JSON output */
        self::assertJsonStringEqualsJsonString(json_encode([
            ['value' => 3, 'currency' => Currency::USD->name],
            ['value' => 4, 'currency' => Currency::USD->name],
            ['value' => 7, 'currency' => Currency::USD->name],
            ['value' => 8, 'currency' => Currency::USD->name]
        ]), $collection->toJson(preserveKeys: PreserveKeys::DISCARD));
        self::assertSame(4, $collection->count());
    }

    public function testFilterMapSortAndCount(): void
    {
        /** @Given a collection with values */
        $collection = Collection::createFrom(elements: [9.00, 3.95, 4.9, 5.0, 5.1, 120.00]);

        /** @When filtering values greater than 5, mapping them to Amounts,
         * sorting by value in ascending order, and counting
         */
        $collection
            ->filter(predicate: fn(float $value): bool => $value > 5)
            ->map(transformations: fn(float $value): Amount => new Amount(value: $value, currency: Currency::BRL))
            ->sort(
                order: Order::ASCENDING_VALUE,
                predicate: fn(Amount $first, Amount $second): int => $first->value <=> $second->value
            );

        /** @Then asserting the values, currency, and count */
        self::assertSame([
            ['value' => 5.1, 'currency' => Currency::BRL->name],
            ['value' => 9.0, 'currency' => Currency::BRL->name],
            ['value' => 120.00, 'currency' => Currency::BRL->name]
        ], $collection->toArray(preserveKeys: PreserveKeys::DISCARD));
        self::assertSame(3, $collection->count());
    }

    public function testMemoryUsageWithGenerators(): void
    {
        /** @Given a large collection with ten thousand elements */
        $largeCollection = Collection::createFrom(elements: range(1, 10000));

        /** @And I record the initial memory usage */
        $initialMemory = memory_get_usage();

        /** @When I iterate through the collection and sum the values */
        $sum = 0;
        foreach ($largeCollection as $value) {
            $sum += $value;
        }

        /** @And I record the final memory usage */
        $finalMemory = memory_get_usage();

        /** @Then the sum should be correct */
        self::assertSame(50005000, $sum);

        /** @And the memory usage should not increase significantly */
        $allowedMemoryIncrease = 1024 * 1024;
        self::assertLessThan(
            $initialMemory + $allowedMemoryIncrease,
            $finalMemory,
            'Memory usage increased too much, indicating elements are loaded unnecessarily.'
        );
    }

    public function testIterationWithoutStoringValues(): void
    {
        /** @Given a large collection with ten thousand elements */
        $largeCollection = Collection::createFrom(elements: range(1, 10000));

        /** @When I iterate through the collection to count values greater than 5000 */
        $count = 0;
        foreach ($largeCollection as $value) {
            if ($value > 5000) {
                $count++;
            }
        }

        /** @Then the count should be correct */
        self::assertSame(5000, $count);
    }

    public function testAddFilterRemoveSortAndToArray(): void
    {
        /** @Given a collection */
        $collection = Collection::createFromEmpty();

        /** @When adding elements, filtering values greater than 5, removing specific values,
         * and sorting by value in descending order
         */
        $collection
            ->add(2, 6, 10, 5)
            ->filter(predicate: fn(int $value): bool => $value > 5)
            ->remove(element: 10)
            ->sort(
                order: Order::DESCENDING_VALUE,
                predicate: fn(int $first, int $second): int => $first <=> $second
            );

        /** @Then asserting the resulting array */
        self::assertSame([6], $collection->toArray());
        self::assertSame(1, $collection->count());
    }

    public function testPerformanceAndMemoryArrayVsCollection(): void
    {
        /** @Given a large dataset */
        $elements = range(1, 50000);

        /** @When performing operations with an array */
        $startArrayTime = microtime(true);
        $startArrayMemory = memory_get_usage();

        $array = $elements;
        $array = array_map(fn(int $value): Amount => new Amount(value: $value, currency: Currency::USD), $array);
        usort($array, fn(Amount $first, Amount $second): int => $first->value <=> $second->value);

        $endArrayTime = microtime(true);
        $endArrayMemory = memory_get_usage();

        /** @Then assert that the array operations are performed */
        $arrayExecutionTime = $endArrayTime - $startArrayTime;
        $arrayMemoryUsage = $endArrayMemory - $startArrayMemory;

        /** @When performing operations with Collection using Generator */
        $startCollectionTime = microtime(true);
        $startCollectionMemory = memory_get_usage();

        $collection = Collection::createFrom(elements: $elements);
        $collection
            ->map(transformations: fn(int $value): Amount => new Amount(value: $value, currency: Currency::USD))
            ->sort(
                order: Order::ASCENDING_VALUE,
                predicate: fn(Amount $first, Amount $second): int => $first->value <=> $second->value
            );

        $endCollectionTime = microtime(true);
        $endCollectionMemory = memory_get_usage();

        /** @Then assert that the collection operations are performed */
        $collectionExecutionTime = $endCollectionTime - $startCollectionTime;
        $collectionMemoryUsage = $endCollectionMemory - $startCollectionMemory;

        /** @Then assert that the collection is faster and uses less memory */
        self::assertLessThan($arrayExecutionTime, $collectionExecutionTime, 'Collection is slower than array.');
        self::assertLessThan($arrayMemoryUsage, $collectionMemoryUsage, 'Collection uses more memory than array.');
    }

    public function testIteratorShouldBeReusedIfNoModification(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When retrieving the iterator for the first time */
        $iterator = $collection->getIterator();

        /** @And calling a method that does not modify the collection */
        $count = $collection->count();

        /** @Then the iterator should not be the same (due to lazy generation) */
        self::assertSame(3, $count);
        self::assertNotSame($iterator, $collection->getIterator());
    }

    public function testIteratorShouldBeRecreatedAfterModification(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When retrieving the iterator for the first time */
        $iterator = $collection->getIterator();

        /** @And modifying the collection */
        $collection->add(elements: 4);

        /** @Then the iterator should be recreated */
        self::assertSame(4, $collection->count());
        self::assertNotSame($iterator, $collection->getIterator());
    }
}
