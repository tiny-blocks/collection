<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Aggregate;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Models\InvoiceSummaries;
use TinyBlocks\Collection\Models\InvoiceSummary;

final class CollectionReduceOperationTest extends TestCase
{
    public function testSumByCustomer(): void
    {
        /** @Given a collection of invoice summaries */
        $summaries = InvoiceSummaries::createFrom(elements: [
            new InvoiceSummary(amount: 100.0, customer: 'Customer A'),
            new InvoiceSummary(amount: 150.5, customer: 'Customer A'),
            new InvoiceSummary(amount: 200.75, customer: 'Customer B')
        ]);

        /** @When summing the amounts for customer 'Customer A' */
        $actual = $summaries->sumByCustomer(customer: 'Customer A');

        /** @Then the total amount for 'Customer A' should be 250.5 */
        self::assertSame(250.5, $actual);
    }

    public function testReduceSumOfNumbers(): void
    {
        /** @Given a collection of numbers */
        $numbers = InvoiceSummaries::createFrom(elements: [1, 2, 3, 4, 5]);

        /** @When reducing the collection to a sum */
        $actual = $numbers->reduce(
            aggregator: fn(int $carry, int $number): int => $carry + $number,
            initial: 0
        );

        /** @Then the sum should be correct */
        self::assertSame(15, $actual);
    }

    public function testReduceProductOfNumbers(): void
    {
        /** @Given a collection of numbers */
        $numbers = InvoiceSummaries::createFrom(elements: [1, 2, 3, 4]);

        /** @When reducing the collection to a product */
        $actual = $numbers->reduce(
            aggregator: fn(int $carry, int $number): int => $carry * $number,
            initial: 1
        );

        /** @Then the product should be correct */
        self::assertSame(24, $actual);
    }

    public function testReduceWhenNoMatchFound(): void
    {
        /** @Given a collection of invoice summaries */
        $summaries = InvoiceSummaries::createFrom(elements: [
            new InvoiceSummary(amount: 100.0, customer: 'Customer A'),
            new InvoiceSummary(amount: 150.5, customer: 'Customer A'),
            new InvoiceSummary(amount: 200.75, customer: 'Customer B')
        ]);

        /** @When reducing the collection for a customer with no match */
        $actual = $summaries
            ->filter(predicates: fn(InvoiceSummary $summary): bool => $summary->customer === 'Customer C')
            ->reduce(aggregator: fn(float $carry, InvoiceSummary $summary): float => $carry + $summary->amount,
                initial: 0.0);

        /** @Then the total amount for 'Customer C' should be zero */
        self::assertSame(0.0, $actual);
    }

    public function testReduceWithMixedDataTypes(): void
    {
        /** @Given a collection with mixed data types */
        $mixedData = InvoiceSummaries::createFrom(elements: [1, 'string', 3.14, true]);

        /** @When reducing the collection by concatenating values as strings */
        $actual = $mixedData->reduce(
            aggregator: fn(string $carry, mixed $value): string => $carry . $value,
            initial: ''
        );

        /** @Then the concatenated string should be correct */
        self::assertSame('1string3.141', $actual);
    }

    public function testReduceSumForEmptyCollection(): void
    {
        /** @Given an empty collection of invoice summaries */
        $summaries = InvoiceSummaries::createFrom(elements: []);

        /** @When reducing an empty collection */
        $actual = $summaries->reduce(
            aggregator: fn(float $carry, InvoiceSummary $summary): float => $carry + $summary->amount,
            initial: 0.0
        );

        /** @Then the total amount should be zero */
        self::assertSame(0.0, $actual);
    }

    public function testReduceWithDifferentInitialValue(): void
    {
        /** @Given a collection of invoice summaries */
        $summaries = InvoiceSummaries::createFrom(elements: [
            new InvoiceSummary(amount: 100.0, customer: 'Customer A'),
            new InvoiceSummary(amount: 150.5, customer: 'Customer A'),
            new InvoiceSummary(amount: 200.75, customer: 'Customer B')
        ]);

        /** @When summing the amounts for customer 'Customer A' with an initial value of 50 */
        $actual = $summaries
            ->filter(predicates: fn(InvoiceSummary $summary): bool => $summary->customer === 'Customer A')
            ->reduce(aggregator: fn(float $carry, InvoiceSummary $summary): float => $carry + $summary->amount,
                initial: 50.0);

        /** @Then the total amount for 'Customer A' should be 300.5 */
        self::assertSame(300.5, $actual);
    }
}
