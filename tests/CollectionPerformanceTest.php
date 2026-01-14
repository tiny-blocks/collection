<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use Generator;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\Currency;

final class CollectionPerformanceTest extends TestCase
{
    public function testArrayVsCollectionWithSortProduceSameResults(): void
    {
        /** @Given a large dataset with 10 thousand elements */
        $elements = range(1, 10_000);

        /** @When performing operations with an array */
        $array = array_map(
            static fn(int $value): Amount => new Amount(value: $value, currency: Currency::USD),
            $elements
        );
        usort($array, static fn(Amount $first, Amount $second): int => $first->value <=> $second->value);

        /** @And performing the same operations with Collection */
        $collection = Collection::createFrom(elements: $elements)
            ->map(static fn(int $value): Amount => new Amount(value: $value, currency: Currency::USD))
            ->sort(
                order: Order::ASCENDING_VALUE,
                predicate: static fn(Amount $first, Amount $second): int => $first->value <=> $second->value
            );

        /** @Then assert that both approaches produce the same results */
        self::assertEquals($array[0]->value, $collection->first()->value);
        self::assertEquals($array[array_key_last($array)]->value, $collection->last()->value);
    }

    public function testCollectionIsEfficientForFirstElementRetrieval(): void
    {
        /** @Given a large dataset with 10 million elements as a generator */
        $createGenerator = static fn(): Generator => (static function (): Generator {
            for ($index = 1; $index <= 10_000_000; $index++) {
                yield $index;
            }
        })();

        /** @When retrieving the first element matching a condition near the beginning */
        $this->forceGarbageCollection();
        $startTime = hrtime(true);
        $startMemory = memory_get_usage(true);

        /** @And filter for elements greater than 100 and get the first one (match at position 101) */
        $firstElement = Collection::createFrom(elements: $createGenerator())
            ->filter(static fn(int $value): bool => $value > 100)
            ->first();

        /** @And end the time and memory measurement */
        $executionTimeInMs = (hrtime(true) - $startTime) / 1_000_000;
        $memoryUsageInMB = (memory_get_usage(true) - $startMemory) / 1024 / 1024;

        /** @Then assert that the first matching element is found */
        self::assertSame(101, $firstElement);

        /** @And assert that execution time is minimal due to early termination */
        self::assertLessThan(
            50.0,
            $executionTimeInMs,
            sprintf('Execution time %.2fms exceeded 50ms limit', $executionTimeInMs)
        );

        /** @And assert that memory usage is minimal due to not materializing all elements */
        self::assertLessThan(
            2.0,
            $memoryUsageInMB,
            sprintf('Memory usage %.2fMB exceeded 2MB limit', $memoryUsageInMB)
        );
    }

    public function testLazyOperationsDoNotMaterializeEntireCollection(): void
    {
        /** @Given a generator that would use massive memory if fully materialized */
        $createLargeGenerator = static fn(): Generator => (static function (): Generator {
            for ($index = 1; $index <= 10_000_000; $index++) {
                yield $index;
            }
        })();

        /** @When applying multiple transformations and getting only the first element */
        $this->forceGarbageCollection();
        $startMemory = memory_get_usage(true);

        /** @And chain filter and map operations */
        $result = Collection::createFrom(elements: $createLargeGenerator())
            ->filter(static fn(int $value): bool => $value % 2 === 0)
            ->map(static fn(int $value): int => $value * 10)
            ->filter(static fn(int $value): bool => $value > 100)
            ->first();

        /** @And measure memory after operation */
        $memoryUsageInMB = (memory_get_usage(true) - $startMemory) / 1024 / 1024;

        /** @Then assert that the correct result is returned */
        self::assertSame(120, $result);

        /** @And assert that memory usage is minimal (not 10 million integers in memory) */
        self::assertLessThan(
            10.0,
            $memoryUsageInMB,
            sprintf(
                'Memory usage %.2fMB is too high - collection may be materializing unnecessarily',
                $memoryUsageInMB
            )
        );
    }

    public function testCollectionFindByIsEfficientWithEarlyTermination(): void
    {
        /** @Given a large dataset with 1 million elements as a generator */
        $createGenerator = static fn(): Generator => (static function (): Generator {
            for ($index = 1; $index <= 1_000_000; $index++) {
                yield new Amount(value: $index, currency: Currency::USD);
            }
        })();

        /** @When finding the first element with value 100 using Collection */
        $this->forceGarbageCollection();
        $startTime = hrtime(true);
        $startMemory = memory_get_usage(true);

        /** @And use findBy to locate the element */
        $foundElement = Collection::createFrom(elements: $createGenerator())
            ->findBy(static fn(Amount $amount): bool => $amount->value == 100.0);

        /** @And end the time and memory measurement */
        $executionTimeInMs = (hrtime(true) - $startTime) / 1_000_000;
        $memoryUsageInMB = (memory_get_usage(true) - $startMemory) / 1024 / 1024;

        /** @Then assert that the correct element is found */
        self::assertSame(100.0, $foundElement->value);

        /** @And assert that execution time is minimal due to early termination */
        self::assertLessThan(
            100.0,
            $executionTimeInMs,
            sprintf('Execution time %.2fms exceeded 100ms limit', $executionTimeInMs)
        );

        /** @And assert that memory usage is minimal */
        self::assertLessThan(
            2.0,
            $memoryUsageInMB,
            sprintf('Memory usage %.2fMB exceeded 2MB limit', $memoryUsageInMB)
        );
    }

    public function testChainedOperationsPerformanceAndMemoryWithCollection(): void
    {
        /** @Given a large collection of Amount objects containing 100 thousand elements */
        $collection = Collection::createFrom(
            elements: (static function (): Generator {
                foreach (range(1, 100_000) as $value) {
                    yield new Amount(value: $value, currency: Currency::USD);
                }
            })()
        );

        /** @And start measuring time and memory usage */
        $this->forceGarbageCollection();
        $startTime = hrtime(true);
        $startMemory = memory_get_usage(true);

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
        $result = $collection
            ->filter(static fn(Amount $amount, int $index): bool => $index < 50_000)
            ->map(static fn(Amount $amount): Amount => new Amount(
                value: $amount->value * 2,
                currency: $amount->currency
            ))
            ->filter(static fn(Amount $amount, int $index): bool => $index < 45_000)
            ->filter(static fn(Amount $amount, int $index): bool => $index < 35_000)
            ->map(static fn(Amount $amount): Amount => new Amount(
                value: $amount->value * 2,
                currency: $amount->currency
            ))
            ->filter(static fn(Amount $amount, int $index): bool => $index < 30_000)
            ->filter(static fn(Amount $amount, int $index): bool => $index < 10_000)
            ->map(static fn(Amount $amount): Amount => new Amount(
                value: $amount->value * 5.5,
                currency: Currency::BRL
            ))
            ->sort(order: Order::DESCENDING_VALUE);

        /** @And force full evaluation by getting first element */
        $firstElement = $result->first();

        /** @And end measuring time and memory usage */
        $executionTimeInSeconds = (hrtime(true) - $startTime) / 1_000_000_000;
        $memoryUsageInMB = (memory_get_usage(true) - $startMemory) / 1024 / 1024;

        /** @Then verify the value of the first element in the sorted collection */
        self::assertSame(220_000.0, $firstElement->value);

        /** @And verify that the total duration of the chained operations is within limits */
        self::assertLessThan(
            15.0,
            $executionTimeInSeconds,
            sprintf('Execution time %.2fs exceeded 15s limit', $executionTimeInSeconds)
        );

        /** @And verify that memory usage is within acceptable limits */
        self::assertLessThan(
            50.0,
            $memoryUsageInMB,
            sprintf('Memory usage %.2fMB exceeded 50MB limit', $memoryUsageInMB)
        );
    }

    public function testCollectionUsesLessMemoryThanArrayForFirstElementRetrieval(): void
    {
        /** @Given a large dataset with 1 million elements */
        $totalElements = 1_000_000;
        $targetValue = 1000;

        /** @When finding the first matching element with an array */
        $this->forceGarbageCollection();
        $startArrayMemory = memory_get_usage(true);

        /** @And create array and find first element greater than target */
        $array = range(1, $totalElements);
        $arrayResult = null;
        foreach ($array as $value) {
            if ($value > $targetValue) {
                $arrayResult = $value;
                break;
            }
        }

        /** @And measure memory usage for the array operations */
        $arrayMemoryUsageInBytes = memory_get_usage(true) - $startArrayMemory;

        /** @And clean up array memory before Collection test */
        unset($array);
        $this->forceGarbageCollection();

        /** @When finding the first matching element with Collection using a generator */
        $startCollectionMemory = memory_get_usage(true);

        /** @And create collection from generator and find first element greater than target */
        $generator = (static function () use ($totalElements): Generator {
            for ($index = 1; $index <= $totalElements; $index++) {
                yield $index;
            }
        })();

        $collectionResult = Collection::createFrom(elements: $generator)
            ->filter(static fn(int $value): bool => $value > $targetValue)
            ->first();

        /** @And measure memory usage for the Collection operations */
        $collectionMemoryUsageInBytes = memory_get_usage(true) - $startCollectionMemory;

        /** @Then assert that both approaches produce the same result */
        self::assertSame($arrayResult, $collectionResult);

        /** @And assert that Collection uses less memory than array */
        self::assertLessThan(
            $arrayMemoryUsageInBytes,
            $collectionMemoryUsageInBytes,
            sprintf(
                'Collection (%d bytes) should use less memory than array (%d bytes)',
                $collectionMemoryUsageInBytes,
                $arrayMemoryUsageInBytes
            )
        );
    }

    private function forceGarbageCollection(): void
    {
        gc_enable();
        gc_collect_cycles();
        gc_mem_caches();
    }
}
