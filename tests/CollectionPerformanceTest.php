<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Internal\Operations\Order\Order;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\Currency;

final class CollectionPerformanceTest extends TestCase
{
    public function testMemoryUsageWithGenerators(): void
    {
        /** @Given a large collection with ten thousand elements */
        $collection = Collection::createFrom(elements: range(1, 10000));

        /** @And I record the initial memory usage */
        $initialMemory = memory_get_usage();

        /** @When I iterate through the collection and sum the values */
        $sum = 0;
        foreach ($collection as $value) {
            $sum += $value;
        }

        /** @And I record the final memory usage */
        $finalMemory = memory_get_usage();

        /** @Then the sum should be correct */
        self::assertSame(50005000, $sum);

        /** @And the memory usage should not increase significantly */
        $allowedMemoryIncrease = 1024 * 1024;
        self::assertLessThan($initialMemory + $allowedMemoryIncrease, $finalMemory);
    }

    public function testPerformanceOnLargeDataSet(): void
    {
        /** @Given a collection with 100 thousand elements */
        $collection = Collection::createFrom(elements: range(1, 100000));

        /** @When Applying a filter operation to extract even numbers */
        $startTime = microtime(true);
        $actual = $collection->filter(predicates: fn(int $value): bool => $value % 2 === 0);
        $endTime = microtime(true);

        /** @Then Verify that the operation completes within acceptable performance limits */
        self::assertLessThan(2, $endTime - $startTime);

        /** @Then Verify that the filtered collection contains the expected number of elements */
        self::assertSame(50000, $actual->count());
    }

    public function testPerformanceOfChainedOperations(): void
    {
        /** @Given a collection with 100 thousand elements */
        $collection = Collection::createFrom(elements: range(1, 100000));

        /** @When applying multiple operations (filter, map, sort) */
        $startTime = microtime(true);
        $actual = $collection
            ->filter(predicates: fn(int $value): bool => $value % 2 === 0)
            ->map(transformations: fn(int $value): int => $value * 2)
            ->sort();
        $endTime = microtime(true);

        /** @Then verify that the operation completes within acceptable performance limits */
        self::assertLessThan(5, $endTime - $startTime);

        /** @Then Verify that the result is correctly sorted and mapped */
        self::assertSame(4, $actual->first());
        self::assertSame(200000, $actual->last());
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
}
