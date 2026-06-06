<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\Carriers;
use Test\TinyBlocks\Collection\Models\Invoice;
use Test\TinyBlocks\Collection\Models\Invoices;
use TinyBlocks\Mapper\Mapper;

final class CollectionMappingTest extends TestCase
{
    public function testToObjectWhenTypedCollectionThenElementsAreBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a list of invoice rows is mapped into an element-typed collection */
        $invoices = $mapper->toObject(type: Invoices::class, source: [
            ['id' => 'INV-001', 'amount' => 100.0, 'customer' => 'Alice'],
            ['id' => 'INV-002', 'amount' => 200.0, 'customer' => 'Bob']
        ]);

        /** @Then the collection holds the matching typed elements */
        self::assertInstanceOf(Invoices::class, $invoices);
        self::assertEquals(new Invoice(id: 'INV-001', amount: 100.0, customer: 'Alice'), $invoices->first());
        self::assertSame(300.0, $invoices->totalAmount());
    }

    public function testToArrayWhenScalarElementsThenValuesAreSerialized(): void
    {
        /** @Given a Carriers collection of scalar names */
        $carriers = Carriers::createFrom(elements: ['DHL', 'FedEx']);

        /** @When the collection is serialized */
        $array = $carriers->toArray();

        /** @Then the scalar elements are emitted as-is */
        self::assertSame(['DHL', 'FedEx'], $array);
    }

    public function testToObjectWhenUntypedCollectionThenElementsArePassedThrough(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a list of names is mapped into a collection without a declared element type */
        $carriers = $mapper->toObject(type: Carriers::class, source: ['DHL', 'FedEx']);

        /** @Then the elements are passed through unchanged */
        self::assertInstanceOf(Carriers::class, $carriers);
        self::assertSame(['DHL', 'FedEx'], $carriers->toArray());
    }

    public function testToObjectAndToArrayWhenTypedCollectionThenRoundTripIsLossless(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And an element-typed collection of invoices */
        $original = Invoices::createFrom(elements: [
            new Invoice(id: 'INV-001', amount: 100.0, customer: 'Alice'),
            new Invoice(id: 'INV-002', amount: 200.0, customer: 'Bob')
        ]);

        /** @When the collection is serialized to rows and rebuilt */
        $rebuilt = $mapper->toObject(type: Invoices::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt collection is element-wise equal to the original */
        self::assertTrue($rebuilt->equals(other: $original));
    }
}
