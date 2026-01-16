<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Operations\Transform;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\Invoice;
use Test\TinyBlocks\Collection\Models\Invoices;
use Test\TinyBlocks\Collection\Models\InvoiceSummaries;
use Test\TinyBlocks\Collection\Models\InvoiceSummary;
use TinyBlocks\Collection\Collection;


final class CollectionEachOperationTest extends TestCase
{
    public function testTransformCollection(): void
    {
        /** @Given a collection of invoices */
        $invoices = Invoices::createFrom(elements: [
            new Invoice(id: 'INV001', amount: 100.0, customer: 'Customer A'),
            new Invoice(id: 'INV002', amount: 150.5, customer: 'Customer B'),
            new Invoice(id: 'INV003', amount: 200.75, customer: 'Customer C')
        ]);

        /** @And an empty collection of invoice summaries */
        $summaries = InvoiceSummaries::createFromEmpty();

        /** @When mapping specific attributes from invoices to invoice summaries */
        $invoices->each(function (Invoice $invoice) use ($summaries): void {
            $summaries->add(new InvoiceSummary(amount: $invoice->amount, customer: $invoice->customer));
        });

        /** @Then the invoice summaries should contain the mapped data */
        self::assertCount(3, $summaries);

        $expected = [
            ['amount' => 100.0, 'customer' => 'Customer A'],
            ['amount' => 150.5, 'customer' => 'Customer B'],
            ['amount' => 200.75, 'customer' => 'Customer C']
        ];

        self::assertEquals($expected, $summaries->toArray());
    }

    public function testEachWithMultipleActions(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When executing multiple actions */
        $result = [];
        $collection->each(
            function (int $value) use (&$result): void {
                $result[] = $value + 1;
            },
            function (int $value) use (&$result): void {
                $result[] = $value * 2;
            }
        );

        /** @Then the result should reflect the actions */
        self::assertSame([2, 2, 3, 4, 4, 6], $result);
    }

    public function testPreserveKeysWithMultipleActions(): void
    {
        /** @Given a collection with associative array elements */
        $collection = Collection::createFrom(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When executing actions and collecting the results */
        $result = [];
        $collection->each(
            function (int $value, string $key) use (&$result): void {
                $result[$key] = $value * 2;
            },
            function (int $value, string $key) use (&$result): void {
                $result[$key] += 1;
            }
        );

        /** @Then the result should contain the modified elements with preserved keys */
        self::assertSame(['a' => 3, 'b' => 5, 'c' => 7], $result);
    }
}
