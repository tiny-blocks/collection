<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\Amount;
use Test\TinyBlocks\Collection\Models\Carriers;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use Test\TinyBlocks\Collection\Models\Dragon;
use Test\TinyBlocks\Collection\Models\Invoice;
use Test\TinyBlocks\Collection\Models\Invoices;
use Test\TinyBlocks\Collection\Models\InvoiceSummaries;
use Test\TinyBlocks\Collection\Models\InvoiceSummary;
use Test\TinyBlocks\Collection\Models\Order;
use Test\TinyBlocks\Collection\Models\Product;
use Test\TinyBlocks\Collection\Models\Products;
use Test\TinyBlocks\Collection\Models\Status;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Order as SortOrder;
use TinyBlocks\Currency\Currency;
use TinyBlocks\Mapper\KeyPreservation;

final class CollectionTest extends TestCase
{
    public function testLazyAndEagerProduceSameResultsWithIntegerPipeline(): void
    {
        /** @Given a set of elements */
        $elements = [5, 3, 1, 4, 2];

        /** @And a filter predicate for values greater than 2 */
        $filter = static fn(int $value): bool => $value > 2;

        /** @And a map transformation that multiplies by 10 */
        $map = static fn(int $value): int => $value * 10;

        /** @When applying filter, map and sort on a lazy collection */
        $lazyResult = Collection::createLazyFrom(elements: $elements)
            ->filter(predicates: $filter)
            ->map(transformations: $map)
            ->sort(order: SortOrder::ASCENDING_VALUE)
            ->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @And applying the same operations on an eager collection */
        $eagerResult = Collection::createFrom(elements: $elements)
            ->filter(predicates: $filter)
            ->map(transformations: $map)
            ->sort(order: SortOrder::ASCENDING_VALUE)
            ->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then both collections should produce identical arrays */
        self::assertSame($lazyResult, $eagerResult);
    }

    public function testLazyAndEagerProduceSameResultsWithObjectPipeline(): void
    {
        /** @Given a set of Amount objects */
        $elements = [
            new Amount(value: 50.00, currency: Currency::USD),
            new Amount(value: 100.00, currency: Currency::USD),
            new Amount(value: 150.00, currency: Currency::USD),
            new Amount(value: 250.00, currency: Currency::USD),
            new Amount(value: 500.00, currency: Currency::USD)
        ];

        /** @And a filter predicate for amounts greater than or equal to 100 */
        $filter = static fn(Amount $amount): bool => $amount->value >= 100;

        /** @And a map transformation that applies a 10% discount */
        $map = static fn(Amount $amount): Amount => new Amount(
            value: $amount->value * 0.9,
            currency: $amount->currency
        );

        /** @And a removeAll predicate for amounts greater than 300 */
        $removeAll = static fn(Amount $amount): bool => $amount->value > 300;

        /** @And a comparator that sorts by value */
        $comparator = static fn(Amount $first, Amount $second): int => $first->value <=> $second->value;

        /** @When applying the operations on a lazy collection */
        $lazy = Collection::createLazyFrom(elements: $elements)
            ->filter(predicates: $filter)
            ->map(transformations: $map)
            ->removeAll(predicate: $removeAll)
            ->sort(order: SortOrder::ASCENDING_VALUE, comparator: $comparator);

        /** @And applying the same operations on an eager collection */
        $eager = Collection::createFrom(elements: $elements)
            ->filter(predicates: $filter)
            ->map(transformations: $map)
            ->removeAll(predicate: $removeAll)
            ->sort(order: SortOrder::ASCENDING_VALUE, comparator: $comparator);

        /** @Then both should have the same count */
        self::assertSame($lazy->count(), $eager->count());

        /** @And the same first value */
        self::assertSame($lazy->first()->value, $eager->first()->value);

        /** @And the same last value */
        self::assertSame($lazy->last()->value, $eager->last()->value);
    }

    public function testConcatLazyWithEager(): void
    {
        /** @Given a lazy collection */
        $lazy = Collection::createLazyFrom(elements: [1, 2]);

        /** @And an eager collection */
        $eager = Collection::createFrom(elements: [3, 4]);

        /** @When concatenating the eager collection into the lazy one */
        $actual = $lazy->merge(other: $eager);

        /** @Then the resulting collection should contain all four elements */
        self::assertSame([1, 2, 3, 4], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testConcatEagerWithLazy(): void
    {
        /** @Given an eager collection */
        $eager = Collection::createFrom(elements: [1, 2]);

        /** @And a lazy collection */
        $lazy = Collection::createLazyFrom(elements: [3, 4]);

        /** @When concatenating the lazy collection into the eager one */
        $actual = $eager->merge(other: $lazy);

        /** @Then the resulting collection should contain all four elements */
        self::assertSame([1, 2, 3, 4], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testEqualsAcrossStrategies(): void
    {
        /** @Given a lazy collection with elements 1, 2, 3 */
        $lazy = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @And an eager collection with elements 1, 2, 3 */
        $eager = Collection::createFrom(elements: [1, 2, 3]);

        /** @When comparing lazy equals eager */
        $lazyEqualsEager = $lazy->equals(other: $eager);

        /** @And comparing eager equals lazy */
        $eagerEqualsLazy = $eager->equals(other: $lazy);

        /** @Then the lazy-to-eager comparison should return true */
        self::assertTrue($lazyEqualsEager);

        /** @And the eager-to-lazy comparison should return true */
        self::assertTrue($eagerEqualsLazy);
    }

    public function testReduceProducesSameResultAcrossStrategies(): void
    {
        /** @Given a set of elements */
        $elements = [1, 2, 3, 4, 5];

        /** @And an accumulator that sums values */
        $accumulator = static fn(int $carry, int $value): int => $carry + $value;

        /** @When reducing with a lazy collection */
        $lazySum = Collection::createLazyFrom(elements: $elements)
            ->reduce(accumulator: $accumulator, initial: 0);

        /** @And reducing with an eager collection */
        $eagerSum = Collection::createFrom(elements: $elements)
            ->reduce(accumulator: $accumulator, initial: 0);

        /** @Then the lazy sum should be 15 */
        self::assertSame(15, $lazySum);

        /** @And the eager sum should be 15 */
        self::assertSame(15, $eagerSum);
    }

    public function testCarriersPreservesTypeAfterFilter(): void
    {
        /** @Given a Carriers collection with three carrier names */
        $carriers = Carriers::createFrom(elements: ['DHL', 'FedEx', 'UPS']);

        /** @When filtering carriers that start with a letter after D */
        $actual = $carriers->filter(
            predicates: static fn(string $name): bool => $name[0] >= 'E'
        );

        /** @Then the result should still be an instance of Carriers */
        self::assertInstanceOf(Carriers::class, $actual);

        /** @And it should contain two carriers */
        self::assertSame(2, $actual->count());

        /** @And the carriers should be FedEx and UPS */
        self::assertSame(['FedEx', 'UPS'], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testCarriersPreservesTypeAfterAdd(): void
    {
        /** @Given a Carriers collection with two carrier names */
        $carriers = Carriers::createFrom(elements: ['DHL', 'FedEx']);

        /** @When adding a new carrier */
        $actual = $carriers->add('Correios');

        /** @Then the result should still be an instance of Carriers */
        self::assertInstanceOf(Carriers::class, $actual);

        /** @And it should contain three carriers */
        self::assertSame(3, $actual->count());
    }

    public function testCarriersLazyPreservesTypeAfterMap(): void
    {
        /** @Given a lazy Carriers collection */
        $carriers = Carriers::createLazyFrom(elements: ['dhl', 'fedex', 'ups']);

        /** @When mapping to uppercase */
        $actual = $carriers->map(
            transformations: static fn(string $name): string => strtoupper($name)
        );

        /** @Then the result should still be an instance of Carriers */
        self::assertInstanceOf(Carriers::class, $actual);

        /** @And the carriers should be uppercased */
        self::assertSame(['DHL', 'FEDEX', 'UPS'], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testInvoicesTotalAmount(): void
    {
        /** @Given a set of invoices */
        $invoices = Invoices::createFrom(elements: [
            new Invoice(id: 'INV-001', amount: 100.00, customer: 'Alice'),
            new Invoice(id: 'INV-002', amount: 200.00, customer: 'Bob'),
            new Invoice(id: 'INV-003', amount: 150.00, customer: 'Alice')
        ]);

        /** @When calculating the total amount */
        $total = $invoices->totalAmount();

        /** @Then the total should be 450 */
        self::assertSame(450.00, $total);
    }

    public function testInvoicesFilterByCustomer(): void
    {
        /** @Given a set of invoices for different customers */
        $invoices = Invoices::createFrom(elements: [
            new Invoice(id: 'INV-001', amount: 100.00, customer: 'Alice'),
            new Invoice(id: 'INV-002', amount: 200.00, customer: 'Bob'),
            new Invoice(id: 'INV-003', amount: 150.00, customer: 'Alice')
        ]);

        /** @When filtering invoices for Alice */
        $aliceInvoices = $invoices->forCustomer(customer: 'Alice');

        /** @Then the result should still be an instance of Invoices */
        self::assertInstanceOf(Invoices::class, $aliceInvoices);

        /** @And Alice should have two invoices */
        self::assertSame(2, $aliceInvoices->count());

        /** @And the total for Alice should be 250 */
        self::assertSame(250.00, $aliceInvoices->totalAmount());
    }

    public function testInvoiceSummariesSumByCustomer(): void
    {
        /** @Given a collection of invoice summaries */
        $summaries = InvoiceSummaries::createFrom(elements: [
            new InvoiceSummary(amount: 100.00, customer: 'Alice'),
            new InvoiceSummary(amount: 200.00, customer: 'Bob'),
            new InvoiceSummary(amount: 150.00, customer: 'Alice'),
            new InvoiceSummary(amount: 300.00, customer: 'Bob')
        ]);

        /** @When summing by customer Alice */
        $aliceTotal = $summaries->sumByCustomer(customer: 'Alice');

        /** @Then Alice's total should be 250 */
        self::assertSame(250.00, $aliceTotal);
    }

    public function testInvoiceSummariesSumByDifferentCustomer(): void
    {
        /** @Given a collection of invoice summaries */
        $summaries = InvoiceSummaries::createFrom(elements: [
            new InvoiceSummary(amount: 100.00, customer: 'Alice'),
            new InvoiceSummary(amount: 200.00, customer: 'Bob'),
            new InvoiceSummary(amount: 150.00, customer: 'Alice'),
            new InvoiceSummary(amount: 300.00, customer: 'Bob')
        ]);

        /** @When summing by customer Bob */
        $bobTotal = $summaries->sumByCustomer(customer: 'Bob');

        /** @Then Bob's total should be 500 */
        self::assertSame(500.00, $bobTotal);
    }

    public function testInvoiceSummariesSumByNonExistentCustomer(): void
    {
        /** @Given a collection of invoice summaries */
        $summaries = InvoiceSummaries::createFrom(elements: [
            new InvoiceSummary(amount: 100.00, customer: 'Alice')
        ]);

        /** @When summing by a customer that does not exist */
        $total = $summaries->sumByCustomer(customer: 'Charlie');

        /** @Then the total should be zero */
        self::assertSame(0.0, $total);
    }

    public function testDragonsGroupByDescription(): void
    {
        /** @Given a collection of dragons */
        $dragons = Collection::createFrom(elements: [
            new Dragon(name: 'Smaug', description: 'fire'),
            new Dragon(name: 'Viserion', description: 'ice'),
            new Dragon(name: 'Drogon', description: 'fire'),
            new Dragon(name: 'Rhaegal', description: 'fire'),
            new Dragon(name: 'Frostfyre', description: 'ice')
        ]);

        /** @When grouping by description */
        $grouped = $dragons->groupBy(
            classifier: static fn(Dragon $dragon): string => $dragon->description
        );

        /** @Then the fire group should contain three dragons */
        $groups = $grouped->toArray();
        self::assertCount(3, $groups['fire']);

        /** @And the ice group should contain two dragons */
        self::assertCount(2, $groups['ice']);
    }

    public function testDragonsFindByName(): void
    {
        /** @Given a collection of dragons */
        $dragons = Collection::createFrom(elements: [
            new Dragon(name: 'Smaug', description: 'fire'),
            new Dragon(name: 'Viserion', description: 'ice'),
            new Dragon(name: 'Drogon', description: 'fire')
        ]);

        /** @When finding the dragon named Viserion */
        $actual = $dragons->findBy(
            predicates: static fn(Dragon $dragon): bool => $dragon->name === 'Viserion'
        );

        /** @Then it should return Viserion */
        self::assertSame('Viserion', $actual->name);

        /** @And Viserion should be an ice dragon */
        self::assertSame('ice', $actual->description);
    }

    public function testCryptoCurrencySortByPrice(): void
    {
        /** @Given a collection of crypto currencies */
        $cryptos = Collection::createFrom(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 50000.00, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 3000.00, symbol: 'ETH'),
            new CryptoCurrency(name: 'Solana', price: 150.00, symbol: 'SOL')
        ]);

        /** @When sorting by price in ascending order */
        $sorted = $cryptos->sort(
            order: SortOrder::ASCENDING_VALUE,
            comparator: static fn(
                CryptoCurrency $first,
                CryptoCurrency $second
            ): int => $first->price <=> $second->price
        );

        /** @Then the cheapest should be Solana */
        self::assertSame('SOL', $sorted->first()->symbol);

        /** @And the most expensive should be Bitcoin */
        self::assertSame('BTC', $sorted->last()->symbol);
    }

    public function testCryptoCurrencyFilterAndMapSymbols(): void
    {
        /** @Given a collection of crypto currencies */
        $cryptos = Collection::createFrom(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 50000.00, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 3000.00, symbol: 'ETH'),
            new CryptoCurrency(name: 'Dogecoin', price: 0.08, symbol: 'DOGE'),
            new CryptoCurrency(name: 'Solana', price: 150.00, symbol: 'SOL')
        ]);

        /** @When filtering currencies above 100 and mapping to symbols */
        $symbols = $cryptos
            ->filter(predicates: static fn(CryptoCurrency $crypto): bool => $crypto->price > 100)
            ->map(transformations: static fn(CryptoCurrency $crypto): string => $crypto->symbol)
            ->sort(order: SortOrder::ASCENDING_VALUE);

        /** @Then the symbols should be BTC, ETH, SOL in alphabetical order */
        self::assertSame(['BTC', 'ETH', 'SOL'], $symbols->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testProductsWithAmountFlattenFromOrders(): void
    {
        /** @Given a set of orders with products */
        $orders = Collection::createFrom(elements: [
            new Order(id: 1, products: new Products(elements: [
                new Product(name: 'Keyboard', amount: new Amount(value: 75.00, currency: Currency::USD)),
                new Product(name: 'Mouse', amount: new Amount(value: 25.00, currency: Currency::USD))
            ])),
            new Order(id: 2, products: new Products(elements: [
                new Product(name: 'Monitor', amount: new Amount(value: 500.00, currency: Currency::USD))
            ]))
        ]);

        /** @When extracting all products and flattening */
        $allProducts = $orders
            ->map(transformations: static fn(Order $order): array => iterator_to_array($order->products))
            ->flatten();

        /** @Then there should be three products */
        self::assertSame(3, $allProducts->count());

        /** @And the total cost should be 600 */
        $total = $allProducts->reduce(
            accumulator: static fn(float $carry, Product $product): float => $carry + $product->amount->value,
            initial: 0.0
        );
        self::assertSame(600.00, $total);
    }

    public function testStatusEnumCollection(): void
    {
        /** @Given a collection of Status enums */
        $statuses = Collection::createFrom(elements: [
            Status::PAID,
            Status::PENDING,
            Status::PAID,
            Status::PAID,
            Status::PENDING
        ]);

        /** @When filtering only PAID statuses */
        $paid = $statuses->filter(
            predicates: static fn(Status $status): bool => $status === Status::PAID
        );

        /** @Then there should be three PAID statuses */
        self::assertSame(3, $paid->count());
    }

    public function testStatusEnumGroupBy(): void
    {
        /** @Given a collection of Status enums */
        $statuses = Collection::createFrom(elements: [
            Status::PAID,
            Status::PENDING,
            Status::PAID,
            Status::PAID,
            Status::PENDING
        ]);

        /** @When grouping by status name */
        $grouped = $statuses->groupBy(
            classifier: static fn(Status $status): string => $status->name
        );

        /** @Then the PAID group should have three entries */
        $groups = $grouped->toArray();
        self::assertCount(3, $groups['PAID']);

        /** @And the PENDING group should have two entries */
        self::assertCount(2, $groups['PENDING']);
    }

    public function testInvoicesLazyStrategyPreservesType(): void
    {
        /** @Given a lazy Invoices collection */
        $invoices = Invoices::createLazyFrom(elements: [
            new Invoice(id: 'INV-001', amount: 100.00, customer: 'Alice'),
            new Invoice(id: 'INV-002', amount: 200.00, customer: 'Bob')
        ]);

        /** @When sorting by amount */
        $sorted = $invoices->sort(
            order: SortOrder::DESCENDING_VALUE,
            comparator: static fn(Invoice $first, Invoice $second): int => $first->amount <=> $second->amount
        );

        /** @Then the result should still be an instance of Invoices */
        self::assertInstanceOf(Invoices::class, $sorted);

        /** @And the first invoice should have the highest amount */
        self::assertSame(200.00, $sorted->first()->amount);
    }

    public function testInvoicesSliceAndCount(): void
    {
        /** @Given a set of five invoices */
        $invoices = Invoices::createFrom(elements: [
            new Invoice(id: 'INV-001', amount: 100.00, customer: 'Alice'),
            new Invoice(id: 'INV-002', amount: 200.00, customer: 'Bob'),
            new Invoice(id: 'INV-003', amount: 300.00, customer: 'Charlie'),
            new Invoice(id: 'INV-004', amount: 400.00, customer: 'Alice'),
            new Invoice(id: 'INV-005', amount: 500.00, customer: 'Bob')
        ]);

        /** @When slicing from offset 1 with length 3 */
        $sliced = $invoices->slice(offset: 1, length: 3);

        /** @Then the result should still be an instance of Invoices */
        self::assertInstanceOf(Invoices::class, $sliced);

        /** @And the sliced collection should have three invoices */
        self::assertSame(3, $sliced->count());

        /** @And the total of the sliced invoices should be 900 */
        self::assertSame(900.00, $sliced->totalAmount());
    }

    public function testInvoicesRemoveSpecificInvoice(): void
    {
        /** @Given a specific invoice to remove */
        $toRemove = new Invoice(id: 'INV-002', amount: 200.00, customer: 'Bob');

        /** @And a set of invoices containing that invoice */
        $invoices = Invoices::createFrom(elements: [
            new Invoice(id: 'INV-001', amount: 100.00, customer: 'Alice'),
            $toRemove,
            new Invoice(id: 'INV-003', amount: 300.00, customer: 'Charlie')
        ]);

        /** @When removing that invoice */
        $actual = $invoices->remove(element: $toRemove);

        /** @Then there should be two invoices remaining */
        self::assertSame(2, $actual->count());

        /** @And the total should be 400 */
        self::assertSame(400.00, $actual->totalAmount());
    }

    public function testDragonsJoinToString(): void
    {
        /** @Given a collection of dragons */
        $dragons = Collection::createFrom(elements: [
            new Dragon(name: 'Smaug', description: 'fire'),
            new Dragon(name: 'Viserion', description: 'ice'),
            new Dragon(name: 'Drogon', description: 'fire')
        ]);

        /** @When mapping to names and joining with a comma */
        $names = $dragons
            ->map(transformations: static fn(Dragon $dragon): string => $dragon->name)
            ->joinToString(separator: ', ');

        /** @Then the result should list all dragon names */
        self::assertSame('Smaug, Viserion, Drogon', $names);
    }

    public function testInvoiceSummariesPreservesTypeAfterFilter(): void
    {
        /** @Given a collection of invoice summaries */
        $summaries = InvoiceSummaries::createFrom(elements: [
            new InvoiceSummary(amount: 100.00, customer: 'Alice'),
            new InvoiceSummary(amount: 200.00, customer: 'Bob')
        ]);

        /** @When filtering for Alice */
        $filtered = $summaries->filter(
            predicates: static fn(InvoiceSummary $summary): bool => $summary->customer === 'Alice'
        );

        /** @Then the result should still be an instance of InvoiceSummaries */
        self::assertInstanceOf(InvoiceSummaries::class, $filtered);

        /** @And it should contain one summary */
        self::assertSame(1, $filtered->count());
    }

    public function testConcatCarriersCollections(): void
    {
        /** @Given a domestic carriers collection */
        $domestic = Carriers::createFrom(elements: ['Correios', 'Jadlog']);

        /** @And an international carriers collection */
        $international = Carriers::createFrom(elements: ['DHL', 'FedEx']);

        /** @When concatenating international into domestic */
        $all = $domestic->merge(other: $international);

        /** @Then the result should still be an instance of Carriers */
        self::assertInstanceOf(Carriers::class, $all);

        /** @And it should contain four carriers */
        self::assertSame(4, $all->count());

        /** @And the carriers should be in the expected order */
        self::assertSame(['Correios', 'Jadlog', 'DHL', 'FedEx'],
            $all->toArray(keyPreservation: KeyPreservation::DISCARD)
        );
    }

    public function testClosureAndLazyAndEagerProduceSameResults(): void
    {
        /** @Given a set of elements */
        $elements = [5, 3, 1, 4, 2];

        /** @And a filter predicate for values greater than 2 */
        $filter = static fn(int $value): bool => $value > 2;

        /** @And a map transformation that multiplies by 10 */
        $map = static fn(int $value): int => $value * 10;

        /** @When applying filter, map and sort on a closure-backed collection */
        $closureResult = Collection::createLazyFromClosure(factory: static function () use ($elements): array {
            return $elements;
        })
            ->filter(predicates: $filter)
            ->map(transformations: $map)
            ->sort(order: SortOrder::ASCENDING_VALUE)
            ->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @And applying the same operations on a lazy collection */
        $lazyResult = Collection::createLazyFrom(elements: $elements)
            ->filter(predicates: $filter)
            ->map(transformations: $map)
            ->sort(order: SortOrder::ASCENDING_VALUE)
            ->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @And applying the same operations on an eager collection */
        $eagerResult = Collection::createFrom(elements: $elements)
            ->filter(predicates: $filter)
            ->map(transformations: $map)
            ->sort(order: SortOrder::ASCENDING_VALUE)
            ->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then all three should produce identical arrays */
        self::assertSame($closureResult, $lazyResult);
        self::assertSame($closureResult, $eagerResult);
    }

    public function testClosureBackedCarriersPreservesType(): void
    {
        /** @Given a closure-backed Carriers collection */
        $carriers = Carriers::createLazyFromClosure(factory: static function (): array {
            return ['dhl', 'fedex', 'ups'];
        });

        /** @When mapping to uppercase */
        $actual = $carriers->map(
            transformations: static fn(string $name): string => strtoupper($name)
        );

        /** @Then the result should still be an instance of Carriers */
        self::assertInstanceOf(Carriers::class, $actual);

        /** @And the carriers should be uppercased */
        self::assertSame(['DHL', 'FEDEX', 'UPS'], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }
}
