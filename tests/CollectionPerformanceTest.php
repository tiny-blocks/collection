<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Internal\Operations\Order\Order;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\Currency;

final class CollectionPerformanceTest extends TestCase
{
    public function testArrayVsCollectionPerformanceAndMemoryComparison(): void
    {
        /** @Given a large dataset with 10 thousand elements */
        $elements = range(1, 10_000);

        /** @When performing operations with an array */
        $startArrayTime = microtime(true);
        $startArrayMemory = memory_get_usage();

        /** @And map elements to Amount objects and sort them in ascending order using array functions */
        $array = $elements;
        $array = array_map(fn(int $value): Amount => new Amount(value: $value, currency: Currency::USD), $array);
        usort($array, fn(Amount $first, Amount $second): int => $first->value <=> $second->value);

        /** @And end the time and memory measurement for the array operations */
        $endArrayTime = microtime(true);
        $endArrayMemory = memory_get_usage();

        /** @Then assert that the array operations are performed and measure performance */
        $arrayExecutionTime = $endArrayTime - $startArrayTime;
        $arrayMemoryUsage = $endArrayMemory - $startArrayMemory;

        /** @When performing operations with Collection using Generator */
        $startCollectionTime = microtime(true);
        $startCollectionMemory = memory_get_usage();

        /** @And map elements to Amount objects and sort them in ascending order using Collection */
        $collection = Collection::createFrom(elements: $elements);
        $collection
            ->map(transformations: fn(int $value): Amount => new Amount(value: $value, currency: Currency::USD))
            ->sort(
                order: Order::ASCENDING_VALUE,
                predicate: fn(Amount $first, Amount $second): int => $first->value <=> $second->value
            );

        /** @And end the time and memory measurement for the Collection operations */
        $endCollectionTime = microtime(true);
        $endCollectionMemory = memory_get_usage();

        /** @Then assert that the Collection operations are performed and measure performance */
        $collectionExecutionTime = $endCollectionTime - $startCollectionTime;
        $collectionMemoryUsage = $endCollectionMemory - $startCollectionMemory;

        /** @And assert that the Collection is faster and uses less memory than the array */
        self::assertLessThan($arrayExecutionTime, $collectionExecutionTime, 'Collection is slower than array.');
        self::assertLessThan($arrayMemoryUsage, $collectionMemoryUsage, 'Collection uses more memory than array.');
    }

    public function testChainedOperationsPerformanceAndMemoryWithCollection(): void
    {
        /** @Given a large collection of Amount objects containing 100 thousand elements */
        $collection = Collection::createFrom(
            elements: (function () {
                foreach (range(1, 100_000) as $value) {
                    yield new Amount(value: $value, currency: Currency::USD);
                }
            })()
        );

        /** @And start measuring time and memory usage */
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        /**
         * @When performing the following chained operations:
         * filtering to retain the first 50,000 elements,
         * mapping to double each Amount's value,
         * filtering to retain the first 45,000 elements,
         * further filtering to retain the first 35,000 elements,
         * mapping to double each Amount's value again,
         * filtering to retain the first 30,000 elements,
         * further filtering to retain the first 10,000 elements,
         * mapping to convert the currency from USD to BRL and adjusting the value by a factor of 5.5,
         * sorting the collection in descending order by value.
         */
        $collection
            ->filter(predicates: fn(Amount $amount, int $index): bool => $index < 50_000)
            ->map(transformations: fn(Amount $amount): Amount => new Amount(
                value: $amount->value * 2,
                currency: $amount->currency
            ))
            ->filter(predicates: fn(Amount $amount, int $index): bool => $index < 45_000)
            ->filter(predicates: fn(Amount $amount, int $index): bool => $index < 35_000)
            ->map(transformations: fn(Amount $amount): Amount => new Amount(
                value: $amount->value * 2,
                currency: $amount->currency
            ))
            ->filter(predicates: fn(Amount $amount, int $index): bool => $index < 30_000)
            ->filter(predicates: fn(Amount $amount, int $index): bool => $index < 10_000)
            ->map(transformations: fn(Amount $amount): Amount => new Amount(
                value: $amount->value * 5.5,
                currency: Currency::BRL
            ))
            ->sort(order: Order::DESCENDING_VALUE);

        /** @Then verify the value of the first element in the sorted collection */
        self::assertSame(220_000.0, $collection->first()->value);

        /** @And end measuring time and memory usage */
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        /** @Then verify that the total duration of the chained operations is within limits */
        self::assertLessThan(7, $endTime - $startTime);

        /** @And verify that memory usage is within acceptable limits */
        $memoryUsageInMB = ($endMemory - $startMemory) / 1024 / 1024;
        self::assertLessThan(0.3, $memoryUsageInMB);
    }
}
