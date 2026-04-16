<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection;

use Generator;
use PHPUnit\Framework\TestCase;
use stdClass;
use Test\TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Order;
use TinyBlocks\Currency\Currency;
use TinyBlocks\Mapper\KeyPreservation;

final class LazyCollectionTest extends TestCase
{
    public function testFromElements(): void
    {
        /** @Given a set of integer elements */
        $elements = [1, 2, 3];

        /** @When creating a lazy collection from those elements */
        $collection = Collection::createLazyFrom(elements: $elements);

        /** @Then the collection should contain all three elements */
        self::assertSame(3, $collection->count());

        /** @And the array should match the original elements */
        self::assertSame([1, 2, 3], $collection->toArray());
    }

    public function testFromEmpty(): void
    {
        /** @When creating a lazy collection without arguments */
        $collection = Collection::createLazyFromEmpty();

        /** @Then the collection should be empty */
        self::assertTrue($collection->isEmpty());

        /** @And the count should be zero */
        self::assertSame(0, $collection->count());
    }

    public function testFromGenerator(): void
    {
        /** @Given a generator that yields three elements */
        $generator = (static function (): Generator {
            yield 1;
            yield 2;
            yield 3;
        })();

        /** @When creating a lazy collection from the generator */
        $collection = Collection::createLazyFrom(elements: $generator);

        /** @Then the collection should contain all three elements */
        self::assertSame(3, $collection->count());
    }

    public function testAdd(): void
    {
        /** @Given a lazy collection with three elements */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When adding two more elements */
        $actual = $collection->add(4, 5);

        /** @Then the new collection should contain five elements */
        self::assertSame(5, $actual->count());

        /** @And the elements should be in the expected order */
        self::assertSame([1, 2, 3, 4, 5], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));

        /** @And the original collection should remain unchanged */
        self::assertSame(3, $collection->count());
    }

    public function testConcat(): void
    {
        /** @Given a first lazy collection */
        $first = Collection::createLazyFrom(elements: [1, 2]);

        /** @And a second lazy collection */
        $second = Collection::createLazyFrom(elements: [3, 4]);

        /** @When concatenating the second into the first */
        $actual = $first->merge(other: $second);

        /** @Then the resulting collection should contain four elements */
        self::assertSame(4, $actual->count());

        /** @And the elements should be in the expected order */
        self::assertSame([1, 2, 3, 4], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testContainsExistingElement(): void
    {
        /** @Given a lazy collection with integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When checking for an element that exists */
        $actual = $collection->contains(element: 2);

        /** @Then it should return true */
        self::assertTrue($actual);
    }

    public function testContainsMissingElement(): void
    {
        /** @Given a lazy collection with integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When checking for an element that does not exist */
        $actual = $collection->contains(element: 99);

        /** @Then it should return false */
        self::assertFalse($actual);
    }

    public function testContainsObject(): void
    {
        /** @Given an Amount object to search for */
        $target = new Amount(value: 100.00, currency: Currency::USD);

        /** @And a lazy collection with Amount objects */
        $collection = Collection::createLazyFrom(elements: [
            new Amount(value: 50.00, currency: Currency::USD),
            new Amount(value: 100.00, currency: Currency::USD),
            new Amount(value: 200.00, currency: Currency::USD)
        ]);

        /** @When checking if the collection contains an equivalent Amount */
        $actual = $collection->contains(element: $target);

        /** @Then it should return true */
        self::assertTrue($actual);
    }

    public function testContainsObjectDoesNotMatchTrueScalar(): void
    {
        /** @Given a lazy collection containing boolean true */
        $collection = Collection::createLazyFrom(elements: [true]);

        /** @When checking if the collection contains an object */
        $actual = $collection->contains(element: new stdClass());

        /** @Then it should return false because object and scalar types differ */
        self::assertFalse($actual);
    }

    public function testCollectionWithObjectDoesNotContainTrueScalar(): void
    {
        /** @Given a lazy collection containing a stdClass object */
        $collection = Collection::createLazyFrom(elements: [new stdClass()]);

        /** @When checking if the collection contains boolean true */
        $actual = $collection->contains(element: true);

        /** @Then it should return false because an object is not a scalar */
        self::assertFalse($actual);
    }

    public function testCount(): void
    {
        /** @Given a lazy collection with five elements */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4, 5]);

        /** @When counting the elements */
        $actual = $collection->count();

        /** @Then it should return 5 */
        self::assertSame(5, $actual);
    }

    public function testFindFirstMatch(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4, 5]);

        /** @When finding the first element greater than 3 */
        $actual = $collection->findBy(predicates: static fn(int $value): bool => $value > 3);

        /** @Then it should return 4 */
        self::assertSame(4, $actual);
    }

    public function testFindFirstMatchAcrossMultiplePredicates(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4, 5]);

        /** @When finding by multiple predicates (OR semantics) */
        $matchTen = static fn(int $value): bool => $value === 10;
        $matchThree = static fn(int $value): bool => $value === 3;

        $actual = $collection->findBy($matchTen, $matchThree);

        /** @Then it should return the first element matching any predicate */
        self::assertSame(3, $actual);
    }

    public function testFindReturnsNullWithoutPredicates(): void
    {
        /** @Given a lazy collection with truthy and falsy values */
        $collection = Collection::createLazyFrom(elements: [0, 1, 2]);

        /** @When finding without predicates */
        $actual = $collection->findBy();

        /** @Then it should return null */
        self::assertNull($actual);
    }

    public function testFindReturnsNullWhenNoMatch(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When finding an element greater than 100 */
        $actual = $collection->findBy(predicates: static fn(int $value): bool => $value > 100);

        /** @Then it should return null */
        self::assertNull($actual);
    }

    public function testEach(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @And a variable to accumulate a sum */
        $sum = 0;

        /** @When using each to accumulate the sum */
        $collection->each(actions: function (int $value) use (&$sum): void {
            $sum += $value;
        });

        /** @Then the sum should be 6 */
        self::assertSame(6, $sum);
    }

    public function testEqualsWithIdenticalCollections(): void
    {
        /** @Given a first lazy collection */
        $first = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @And a second lazy collection with the same elements */
        $second = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When comparing them for equality */
        $actual = $first->equals(other: $second);

        /** @Then they should be equal */
        self::assertTrue($actual);
    }

    public function testEqualsWithDifferentCollections(): void
    {
        /** @Given a first lazy collection */
        $first = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @And a second lazy collection with different elements */
        $second = Collection::createLazyFrom(elements: [1, 2, 4]);

        /** @When comparing them for equality */
        $actual = $first->equals(other: $second);

        /** @Then they should not be equal */
        self::assertFalse($actual);
    }

    public function testEqualsWithDifferentSizes(): void
    {
        /** @Given a first lazy collection with three elements */
        $first = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @And a second lazy collection with two elements */
        $second = Collection::createLazyFrom(elements: [1, 2]);

        /** @When comparing first equals second */
        $firstEqualsSecond = $first->equals(other: $second);

        /** @And comparing second equals first */
        $secondEqualsFirst = $second->equals(other: $first);

        /** @Then the first comparison should return false */
        self::assertFalse($firstEqualsSecond);

        /** @And the second comparison should return false */
        self::assertFalse($secondEqualsFirst);
    }

    public function testEqualsWithDifferentSizesButSamePrefix(): void
    {
        /** @Given a first lazy collection with four elements */
        $first = Collection::createLazyFrom(elements: [1, 2, 3, 4]);

        /** @And a second lazy collection with three elements */
        $second = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When comparing them for equality */
        $actual = $first->equals(other: $second);

        /** @Then they should not be equal */
        self::assertFalse($actual);
    }

    public function testEqualsWithNullElementsAndDifferentSizes(): void
    {
        /** @Given a first lazy collection with three elements */
        $first = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @And a second lazy collection with four elements ending with null */
        $second = Collection::createLazyFrom(elements: [1, 2, 3, null]);

        /** @When comparing them for equality */
        $actual = $first->equals(other: $second);

        /** @Then they should not be equal */
        self::assertFalse($actual);
    }

    public function testRemoveElement(): void
    {
        /** @Given a lazy collection with duplicate elements */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 2, 4]);

        /** @When removing the value 2 */
        $actual = $collection->remove(element: 2);

        /** @Then all occurrences of 2 should be removed */
        self::assertSame([1, 3, 4], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testRemoveScalarFromObjectCollection(): void
    {
        /** @Given a lazy collection with Amount objects */
        $collection = Collection::createLazyFrom(elements: [
            new Amount(value: 50.00, currency: Currency::USD),
            new Amount(value: 100.00, currency: Currency::USD)
        ]);

        /** @When removing a scalar value */
        $actual = $collection->remove(element: 50.00);

        /** @Then no elements should be removed */
        self::assertSame(2, $actual->count());
    }

    public function testRemovePreservesKeys(): void
    {
        /** @Given a lazy collection with string keys */
        $collection = Collection::createLazyFrom(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When removing the value 2 */
        $actual = $collection->remove(element: 2);

        /** @Then the remaining keys should be preserved */
        self::assertSame(['a' => 1, 'c' => 3], $actual->toArray());
    }

    public function testRemoveAllWithPredicate(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4, 5]);

        /** @When removing all elements greater than 3 */
        $actual = $collection->removeAll(predicate: static fn(int $value): bool => $value > 3);

        /** @Then only elements 1, 2, 3 should remain */
        self::assertSame([1, 2, 3], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testRemoveAllWithoutPredicate(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When removing all without a predicate */
        $actual = $collection->removeAll();

        /** @Then the collection should be empty */
        self::assertSame(0, $actual->count());
    }

    public function testRemoveAllWithNonMatchingFirstElement(): void
    {
        /** @Given a lazy collection where the first element does not match the predicate */
        $collection = Collection::createLazyFrom(elements: [1, 10, 2, 20, 3]);

        /** @When removing all elements greater than 5 */
        $actual = $collection->removeAll(predicate: static fn(int $value): bool => $value > 5);

        /** @Then elements 1, 2, 3 should remain */
        self::assertSame([1, 2, 3], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testRemoveAllPreservesKeys(): void
    {
        /** @Given a lazy collection with string keys */
        $collection = Collection::createLazyFrom(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When removing elements greater than 2 */
        $actual = $collection->removeAll(predicate: static fn(int $value): bool => $value > 2);

        /** @Then the remaining keys should be preserved */
        self::assertSame(['a' => 1, 'b' => 2], $actual->toArray());
    }

    public function testFirstReturnsElement(): void
    {
        /** @Given a lazy collection with three elements */
        $collection = Collection::createLazyFrom(elements: [10, 20, 30]);

        /** @When retrieving the first element */
        $actual = $collection->first();

        /** @Then it should return 10 */
        self::assertSame(10, $actual);
    }

    public function testFirstReturnsDefaultWhenEmpty(): void
    {
        /** @Given an empty lazy collection */
        $collection = Collection::createLazyFromEmpty();

        /** @When retrieving the first element with a default */
        $actual = $collection->first(defaultValueIfNotFound: 'fallback');

        /** @Then it should return the default value */
        self::assertSame('fallback', $actual);
    }

    public function testFirstReturnsNullWhenEmpty(): void
    {
        /** @Given an empty lazy collection */
        $collection = Collection::createLazyFromEmpty();

        /** @When retrieving the first element without a default */
        $actual = $collection->first();

        /** @Then it should return null */
        self::assertNull($actual);
    }

    public function testFirstReturnsNullElementInsteadOfDefault(): void
    {
        /** @Given a lazy collection where the first element is null */
        $collection = Collection::createLazyFrom(elements: [null, 1, 2]);

        /** @When retrieving the first element with a default */
        $actual = $collection->first(defaultValueIfNotFound: 'fallback');

        /** @Then it should return null, not the default */
        self::assertNull($actual);
    }

    public function testFlatten(): void
    {
        /** @Given a lazy collection with nested arrays */
        $collection = Collection::createLazyFrom(elements: [[1, 2], [3, 4], 5]);

        /** @When flattening by one level */
        $actual = $collection->flatten();

        /** @Then all elements should be at the top level */
        self::assertSame([1, 2, 3, 4, 5], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testGetByIndex(): void
    {
        /** @Given a lazy collection with three elements */
        $collection = Collection::createLazyFrom(elements: ['a', 'b', 'c']);

        /** @When retrieving the element at index 1 */
        $actual = $collection->getBy(index: 1);

        /** @Then it should return 'b' */
        self::assertSame('b', $actual);
    }

    public function testGetByIndexReturnsDefaultWhenOutOfBounds(): void
    {
        /** @Given a lazy collection with three elements */
        $collection = Collection::createLazyFrom(elements: ['a', 'b', 'c']);

        /** @When retrieving an element at an index that does not exist */
        $actual = $collection->getBy(index: 99, defaultValueIfNotFound: 'missing');

        /** @Then it should return the default value */
        self::assertSame('missing', $actual);
    }

    public function testGroupBy(): void
    {
        /** @Given a lazy collection of integers from 1 to 6 */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4, 5, 6]);

        /** @When grouping by even and odd */
        $actual = $collection->groupBy(
            classifier: static fn(int $value): string => $value % 2 === 0 ? 'even' : 'odd'
        );

        /** @Then the odd group should contain 1, 3, 5 */
        $groups = $actual->toArray();
        self::assertSame([1, 3, 5], $groups['odd']);

        /** @And the even group should contain 2, 4, 6 */
        self::assertSame([2, 4, 6], $groups['even']);
    }

    public function testIsEmpty(): void
    {
        /** @Given an empty lazy collection */
        $empty = Collection::createLazyFromEmpty();

        /** @Then the empty collection should return true */
        self::assertTrue($empty->isEmpty());
    }

    public function testIsNotEmpty(): void
    {
        /** @Given a non-empty lazy collection */
        $nonEmpty = Collection::createLazyFrom(elements: [1]);

        /** @Then the non-empty collection should return false */
        self::assertFalse($nonEmpty->isEmpty());
    }

    public function testJoinToString(): void
    {
        /** @Given a lazy collection of strings */
        $collection = Collection::createLazyFrom(elements: ['a', 'b', 'c']);

        /** @When joining with a comma separator */
        $actual = $collection->joinToString(separator: ', ');

        /** @Then the result should be "a, b, c" */
        self::assertSame('a, b, c', $actual);
    }

    public function testJoinToStringWithIntegers(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When joining with a comma separator */
        $actual = $collection->joinToString(separator: ', ');

        /** @Then the result should be "1, 2, 3" */
        self::assertSame('1, 2, 3', $actual);
    }

    public function testJoinToStringWithSingleInteger(): void
    {
        /** @Given a lazy collection with a single integer */
        $collection = Collection::createLazyFrom(elements: [42]);

        /** @When joining to string */
        $actual = $collection->joinToString(separator: ', ');

        /** @Then the result should be a string */
        self::assertSame('42', $actual);
    }

    public function testFilterWithPredicate(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4, 5]);

        /** @When keeping only elements greater than 3 */
        $actual = $collection->filter(predicates: static fn(int $value): bool => $value > 3);

        /** @Then only 4 and 5 should remain */
        self::assertSame([4, 5], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testFilterWithoutPredicateRemovesFalsyValues(): void
    {
        /** @Given a lazy collection with falsy and truthy values */
        $collection = Collection::createLazyFrom(elements: [0, '', null, false, 1, 'hello', 2]);

        /** @When filtering without a predicate */
        $actual = $collection->filter();

        /** @Then only truthy values should remain */
        self::assertSame([1, 'hello', 2], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testFilterWithExplicitNull(): void
    {
        /** @Given a lazy collection with falsy and truthy values */
        $collection = Collection::createLazyFrom(elements: [0, '', 1, 'hello', 2]);

        /** @When filtering with an explicit null predicate */
        $actual = $collection->filter(null);

        /** @Then only truthy values should remain */
        self::assertSame([1, 'hello', 2], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testFilterPreservesKeys(): void
    {
        /** @Given a lazy collection with string keys */
        $collection = Collection::createLazyFrom(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When filtering only elements greater than 1 */
        $actual = $collection->filter(predicates: static fn(int $value): bool => $value > 1);

        /** @Then the remaining keys should be preserved */
        self::assertSame(['b' => 2, 'c' => 3], $actual->toArray());
    }

    public function testFilterWithMultiplePredicatesRetainsOnlyMatchingAll(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        /** @When filtering with two predicates: greater than 3 and even */
        $actual = $collection->filter(
            static fn(int $value): bool => $value > 3,
            static fn(int $value): bool => $value % 2 === 0
        );

        /** @Then only elements satisfying both predicates should remain */
        self::assertSame([4, 6, 8, 10], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testLastReturnsElement(): void
    {
        /** @Given a lazy collection with three elements */
        $collection = Collection::createLazyFrom(elements: [10, 20, 30]);

        /** @When retrieving the last element */
        $actual = $collection->last();

        /** @Then it should return 30 */
        self::assertSame(30, $actual);
    }

    public function testLastReturnsDefaultWhenEmpty(): void
    {
        /** @Given an empty lazy collection */
        $collection = Collection::createLazyFromEmpty();

        /** @When retrieving the last element with a default */
        $actual = $collection->last(defaultValueIfNotFound: 'fallback');

        /** @Then it should return the default value */
        self::assertSame('fallback', $actual);
    }

    public function testLastReturnsNullElementInsteadOfDefault(): void
    {
        /** @Given a lazy collection where the last element is null */
        $collection = Collection::createLazyFrom(elements: [1, 2, null]);

        /** @When retrieving the last element with a default */
        $actual = $collection->last(defaultValueIfNotFound: 'fallback');

        /** @Then it should return null, not the default */
        self::assertNull($actual);
    }

    public function testMap(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When transforming each element by multiplying by 10 */
        $actual = $collection->map(transformations: static fn(int $value): int => $value * 10);

        /** @Then each element should be multiplied */
        self::assertSame([10, 20, 30], $actual->toArray());
    }

    public function testMapPreservesKeys(): void
    {
        /** @Given a lazy collection with string keys */
        $collection = Collection::createLazyFrom(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When transforming each element */
        $actual = $collection->map(transformations: static fn(int $value): int => $value * 10);

        /** @Then the keys should be preserved */
        self::assertSame(['a' => 10, 'b' => 20, 'c' => 30], $actual->toArray());
    }

    public function testReduce(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4]);

        /** @When reducing to calculate the sum */
        $actual = $collection->reduce(
            accumulator: static fn(int $carry, int $value): int => $carry + $value,
            initial: 0
        );

        /** @Then the sum should be 10 */
        self::assertSame(10, $actual);
    }

    public function testSortAscending(): void
    {
        /** @Given a lazy collection with unordered elements */
        $collection = Collection::createLazyFrom(elements: [3, 1, 2]);

        /** @When sorting in ascending order by value */
        $actual = $collection->sort(order: Order::ASCENDING_VALUE);

        /** @Then the elements should be in ascending order */
        self::assertSame([1, 2, 3], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testSortDescending(): void
    {
        /** @Given a lazy collection with ordered elements */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When sorting in descending order by value */
        $actual = $collection->sort(order: Order::DESCENDING_VALUE);

        /** @Then the elements should be in descending order */
        self::assertSame([3, 2, 1], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testSortAscendingKey(): void
    {
        /** @Given a lazy collection with unordered string keys */
        $collection = Collection::createLazyFrom(elements: ['c' => 3, 'a' => 1, 'b' => 2]);

        /** @When sorting by ascending key */
        $actual = $collection->sort();

        /** @Then the keys should be in ascending order */
        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $actual->toArray());
    }

    public function testSortDescendingKey(): void
    {
        /** @Given a lazy collection with ordered string keys */
        $collection = Collection::createLazyFrom(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When sorting by descending key */
        $actual = $collection->sort(order: Order::DESCENDING_KEY);

        /** @Then the keys should be in descending order */
        self::assertSame(['c' => 3, 'b' => 2, 'a' => 1], $actual->toArray());
    }

    public function testSortAscendingValueWithoutComparator(): void
    {
        /** @Given a lazy collection with unordered integers */
        $collection = Collection::createLazyFrom(elements: [3, 1, 4, 1, 5]);

        /** @When sorting ascending by value without a custom comparator */
        $actual = $collection->sort(order: Order::ASCENDING_VALUE);

        /** @Then the elements should be sorted by the default spaceship operator */
        self::assertSame([1, 1, 3, 4, 5], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testSortWithCustomComparator(): void
    {
        /** @Given a lazy collection of Amount objects */
        $collection = Collection::createLazyFrom(elements: [
            new Amount(value: 300.00, currency: Currency::USD),
            new Amount(value: 100.00, currency: Currency::USD),
            new Amount(value: 200.00, currency: Currency::USD)
        ]);

        /** @When sorting ascending by value with a custom comparator */
        $actual = $collection->sort(
            order: Order::ASCENDING_VALUE,
            comparator: static fn(Amount $first, Amount $second): int => $first->value <=> $second->value
        );

        /** @Then the first element should have the lowest value */
        self::assertSame(100.00, $actual->first()->value);

        /** @And the last element should have the highest value */
        self::assertSame(300.00, $actual->last()->value);
    }

    public function testSortWithCustomComparatorProducesDifferentOrderThanDefault(): void
    {
        /** @Given a lazy collection where alphabetical and length order diverge */
        $collection = Collection::createLazyFrom(elements: ['zz', 'a', 'bbb']);

        /** @When sorting ascending by length */
        $byLength = $collection->sort(
            order: Order::ASCENDING_VALUE,
            comparator: static fn(string $first, string $second): int => strlen($first) <=> strlen($second)
        );

        /** @And sorting ascending by default (alphabetical) */
        $byDefault = $collection->sort(order: Order::ASCENDING_VALUE);

        /** @Then the custom order should be by length */
        self::assertSame(['a', 'zz', 'bbb'], $byLength->toArray(keyPreservation: KeyPreservation::DISCARD));

        /** @And the default order should be alphabetical */
        self::assertSame(['a', 'bbb', 'zz'], $byDefault->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testSlice(): void
    {
        /** @Given a lazy collection of five elements */
        $collection = Collection::createLazyFrom(elements: [10, 20, 30, 40, 50]);

        /** @When slicing from offset 1 with length 2 */
        $actual = $collection->slice(offset: 1, length: 2);

        /** @Then the result should contain elements 20 and 30 */
        self::assertSame([20, 30], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testSliceUntilEnd(): void
    {
        /** @Given a lazy collection of five elements */
        $collection = Collection::createLazyFrom(elements: [10, 20, 30, 40, 50]);

        /** @When slicing from offset 2 without specifying length */
        $actual = $collection->slice(offset: 2);

        /** @Then the result should contain all elements from index 2 onward */
        self::assertSame([30, 40, 50], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testSlicePreservesKeys(): void
    {
        /** @Given a lazy collection with string keys */
        $collection = Collection::createLazyFrom(elements: ['a' => 10, 'b' => 20, 'c' => 30, 'd' => 40]);

        /** @When slicing from offset 1 with length 2 */
        $actual = $collection->slice(offset: 1, length: 2);

        /** @Then the keys should be preserved */
        self::assertSame(['b' => 20, 'c' => 30], $actual->toArray());
    }

    public function testSliceWithZeroLengthReturnsEmpty(): void
    {
        /** @Given a lazy collection with five elements */
        $collection = Collection::createLazyFrom(elements: [10, 20, 30, 40, 50]);

        /** @When slicing with length zero */
        $actual = $collection->slice(offset: 0, length: 0);

        /** @Then the result should be empty */
        self::assertTrue($actual->isEmpty());

        /** @And the count should be zero */
        self::assertSame(0, $actual->count());
    }

    public function testSliceWithNegativeLengthExcludesTrailingElements(): void
    {
        /** @Given a lazy collection with five elements */
        $collection = Collection::createLazyFrom(elements: [10, 20, 30, 40, 50]);

        /** @When slicing from offset 0 with length -2 (exclude last 2) */
        $actual = $collection->slice(offset: 0, length: -2);

        /** @Then the result should contain the first three elements */
        self::assertSame([10, 20, 30], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testSliceWithOffsetAndNegativeLength(): void
    {
        /** @Given a lazy collection with five elements */
        $collection = Collection::createLazyFrom(elements: [10, 20, 30, 40, 50]);

        /** @When slicing from offset 1 with length -2 (skip first, exclude last 2) */
        $actual = $collection->slice(offset: 1, length: -2);

        /** @Then the result should contain elements 20 and 30 */
        self::assertSame([20, 30], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testSliceWithNegativeLengthPreservesKeys(): void
    {
        /** @Given a lazy collection with string keys */
        $collection = Collection::createLazyFrom(elements: ['a' => 10, 'b' => 20, 'c' => 30, 'd' => 40]);

        /** @When slicing from offset 0 with length -2 */
        $actual = $collection->slice(offset: 0, length: -2);

        /** @Then the keys should be preserved */
        self::assertSame(['a' => 10, 'b' => 20], $actual->toArray());
    }

    public function testSliceWithNegativeLengthProducesExactCount(): void
    {
        /** @Given a lazy collection with six elements */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3, 4, 5, 6]);

        /** @When slicing from offset 0 with length -3 (exclude last 3) */
        $actual = $collection->slice(offset: 0, length: -3);

        /** @Then the collection should contain exactly 3 elements */
        self::assertCount(3, $actual);

        /** @And the elements should be 1, 2, 3 */
        self::assertSame([1, 2, 3], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testToArrayPreservingKeys(): void
    {
        /** @Given a lazy collection with non-sequential keys */
        $collection = Collection::createLazyFrom(elements: [0 => 'a', 2 => 'b', 5 => 'c']);

        /** @When converting to array preserving keys */
        $actual = $collection->toArray();

        /** @Then the keys should be preserved */
        self::assertSame([0 => 'a', 2 => 'b', 5 => 'c'], $actual);
    }

    public function testToArrayDiscardingKeys(): void
    {
        /** @Given a lazy collection with non-sequential keys */
        $collection = Collection::createLazyFrom(elements: [0 => 'a', 2 => 'b', 5 => 'c']);

        /** @When converting to array discarding keys */
        $actual = $collection->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the keys should be re-indexed from 0 */
        self::assertSame(['a', 'b', 'c'], $actual);
    }

    public function testToJson(): void
    {
        /** @Given a lazy collection of integers */
        $collection = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When converting to JSON */
        $actual = $collection->toJson();

        /** @Then the result should be a valid JSON array */
        self::assertSame('[1,2,3]', $actual);
    }

    public function testToJsonDiscardingKeys(): void
    {
        /** @Given a lazy collection with string keys */
        $collection = Collection::createLazyFrom(elements: ['x' => 1, 'y' => 2]);

        /** @When converting to JSON discarding keys */
        $actual = $collection->toJson(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the result should be a sequential JSON array */
        self::assertSame('[1,2]', $actual);
    }

    public function testToJsonPreservesKeysByDefault(): void
    {
        /** @Given a lazy collection with string keys */
        $collection = Collection::createLazyFrom(elements: ['x' => 1, 'y' => 2]);

        /** @When converting to JSON without arguments */
        $actual = $collection->toJson();

        /** @Then the result should preserve keys as a JSON object */
        self::assertSame('{"x":1,"y":2}', $actual);
    }

    public function testImmutability(): void
    {
        /** @Given a lazy collection with three elements */
        $original = Collection::createLazyFrom(elements: [1, 2, 3]);

        /** @When adding a new element */
        $modified = $original->add(4);

        /** @Then the original collection should remain unchanged */
        self::assertSame(3, $original->count());

        /** @And the new collection should have four elements */
        self::assertSame(4, $modified->count());
    }

    public function testChainedOperationsWithObjects(): void
    {
        /** @Given a lazy collection of Amount objects */
        $collection = Collection::createLazyFrom(elements: [
            new Amount(value: 50.00, currency: Currency::USD),
            new Amount(value: 100.00, currency: Currency::USD),
            new Amount(value: 150.00, currency: Currency::USD),
            new Amount(value: 250.00, currency: Currency::USD),
            new Amount(value: 500.00, currency: Currency::USD)
        ]);

        /** @And a variable to accumulate the total discounted value */
        $totalDiscounted = 0.0;

        /** @When chaining filter, map, removeAll and sort */
        $actual = $collection
            ->filter(predicates: static fn(Amount $amount): bool => $amount->value >= 100)
            ->map(transformations: static fn(Amount $amount): Amount => new Amount(
                value: $amount->value * 0.9,
                currency: $amount->currency
            ))
            ->removeAll(predicate: static fn(Amount $amount): bool => $amount->value > 300)
            ->sort(
                order: Order::ASCENDING_VALUE,
                comparator: static fn(Amount $first, Amount $second): int => $first->value <=> $second->value
            );

        /** @And accumulating the total discounted value via each */
        $actual->each(actions: function (Amount $amount) use (&$totalDiscounted): void {
            $totalDiscounted += $amount->value;
        });

        /** @Then the final collection should contain exactly three elements */
        self::assertCount(3, $actual);

        /** @And the total discounted value should be 450 */
        self::assertSame(450.00, $totalDiscounted);

        /** @And the first Amount should be 90 after the discount */
        self::assertSame(90.00, $actual->first()->value);

        /** @And the last Amount should be 225 after the discount */
        self::assertSame(225.00, $actual->last()->value);
    }

    public function testChainedOperationsWithIntegers(): void
    {
        /** @Given a lazy collection of integers from 1 to 100 */
        $collection = Collection::createLazyFrom(elements: range(1, 100));

        /** @When keeping even numbers, squaring them, and sorting in descending order */
        $actual = $collection
            ->filter(predicates: static fn(int $value): bool => $value % 2 === 0)
            ->map(transformations: static fn(int $value): int => $value ** 2)
            ->sort(order: Order::DESCENDING_VALUE);

        /** @Then the first element should be 10000 (square of 100) */
        self::assertSame(10000, $actual->first());

        /** @And the last element should be 4 (square of 2) */
        self::assertSame(4, $actual->last());

        /** @When reducing to calculate the sum of all squared even numbers */
        $sum = $actual->reduce(
            accumulator: static fn(int $carry, int $value): int => $carry + $value,
            initial: 0
        );

        /** @Then the sum should be 171700 */
        self::assertSame(171700, $sum);
    }

    public function testFromClosure(): void
    {
        /** @Given a closure that yields three elements */
        $factory = static function (): Generator {
            yield 1;
            yield 2;
            yield 3;
        };

        /** @When creating a lazy collection from the closure */
        $collection = Collection::createLazyFromClosure(factory: $factory);

        /** @Then the collection should contain all three elements */
        self::assertSame(3, $collection->count());

        /** @And the array should match the expected elements */
        self::assertSame([1, 2, 3], $collection->toArray());
    }

    public function testFromClosureReiteratesSuccessfully(): void
    {
        /** @Given a closure that yields elements */
        $collection = Collection::createLazyFromClosure(factory: static function (): Generator {
            yield 10;
            yield 20;
            yield 30;
        });

        /** @When consuming the collection via count */
        $count = $collection->count();

        /** @Then the count should be 3 */
        self::assertSame(3, $count);

        /** @And a subsequent toArray should still return all elements */
        self::assertSame([10, 20, 30], $collection->toArray());

        /** @And first should return the first element */
        self::assertSame(10, $collection->first());

        /** @And last should return the last element */
        self::assertSame(30, $collection->last());
    }

    public function testFromClosureWithEmptyClosure(): void
    {
        /** @Given a closure that yields nothing */
        $collection = Collection::createLazyFromClosure(factory: static function (): Generator {
            yield from [];
        });

        /** @When checking the collection */
        $isEmpty = $collection->isEmpty();

        /** @Then the collection should be empty */
        self::assertTrue($isEmpty);

        /** @And the count should be zero */
        self::assertSame(0, $collection->count());
    }

    public function testFromClosureWithChainedOperations(): void
    {
        /** @Given a closure-backed collection with integers */
        $collection = Collection::createLazyFromClosure(factory: static function (): Generator {
            yield from [5, 3, 1, 4, 2];
        });

        /** @When chaining filter, map and sort */
        $actual = $collection
            ->filter(predicates: static fn(int $value): bool => $value > 2)
            ->map(transformations: static fn(int $value): int => $value * 10)
            ->sort(order: Order::ASCENDING_VALUE);

        /** @Then the result should contain the filtered, mapped and sorted values */
        self::assertSame([30, 40, 50], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testFromClosureWithObjects(): void
    {
        /** @Given a closure that yields Amount objects */
        $collection = Collection::createLazyFromClosure(factory: static function (): Generator {
            yield new Amount(value: 100.00, currency: Currency::USD);
            yield new Amount(value: 200.00, currency: Currency::USD);
            yield new Amount(value: 300.00, currency: Currency::USD);
        });

        /** @When reducing to sum all amounts */
        $total = $collection->reduce(
            accumulator: static fn(float $carry, Amount $amount): float => $carry + $amount->value,
            initial: 0.0
        );

        /** @Then the total should be 600 */
        self::assertSame(600.00, $total);
    }

    public function testFromClosureGetByIndex(): void
    {
        /** @Given a closure-backed collection */
        $collection = Collection::createLazyFromClosure(factory: static function (): Generator {
            yield 'alpha';
            yield 'beta';
            yield 'gamma';
        });

        /** @When retrieving element at index 1 */
        $actual = $collection->getBy(index: 1);

        /** @Then it should return the second element */
        self::assertSame('beta', $actual);
    }

    public function testFromClosureContainsElement(): void
    {
        /** @Given a closure-backed collection */
        $collection = Collection::createLazyFromClosure(factory: static function (): Generator {
            yield 'alpha';
            yield 'beta';
            yield 'gamma';
        });

        /** @When checking if the collection contains an existing element */
        $containsBeta = $collection->contains(element: 'beta');

        /** @Then it should return true */
        self::assertTrue($containsBeta);

        /** @And checking for a non-existing element should return false */
        self::assertFalse($collection->contains(element: 'delta'));
    }

    public function testFromClosureAdd(): void
    {
        /** @Given a closure-backed collection */
        $collection = Collection::createLazyFromClosure(factory: static function (): Generator {
            yield 1;
            yield 2;
        });

        /** @When adding elements */
        $actual = $collection->add(3, 4);

        /** @Then all elements should be present */
        self::assertSame([1, 2, 3, 4], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }

    public function testFromClosureMerge(): void
    {
        /** @Given a closure-backed collection */
        $closureCollection = Collection::createLazyFromClosure(factory: static function (): Generator {
            yield 1;
            yield 2;
        });

        /** @And an eager collection */
        $eagerCollection = Collection::createFrom(elements: [3, 4]);

        /** @When merging them */
        $actual = $closureCollection->merge(other: $eagerCollection);

        /** @Then the result should contain all elements */
        self::assertSame([1, 2, 3, 4], $actual->toArray(keyPreservation: KeyPreservation::DISCARD));
    }
}
