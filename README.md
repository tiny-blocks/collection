# Collection

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    * [Writing](#writing)
    * [Filtering](#filtering)
    * [Ordering](#ordering)
    * [Retrieving](#retrieving)
    * [Comparing](#comparing)
    * [Aggregation](#aggregation)
    * [Transforming](#transforming)
* [Evaluation strategies](#evaluation-strategies)
* [FAQ](#faq)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div> 

## Overview

The `Collection` library provides a flexible and efficient API to manipulate, iterate, and manage collections in a
structured and type-safe manner.

It leverages [PHP's Generators](https://www.php.net/manual/en/language.generators.overview.php) for optimized memory
usage and lazy evaluation, ensuring that large datasets are handled efficiently without loading all
elements into memory at once.

The library supports adding, removing, filtering, sorting, and transforming elements.

<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/collection
```

<div id='how-to-use'></div>

## How to use

The library exposes the available behaviors through the `Collectible` interface and provides utilities to manipulate
collections of various types.

### Concrete implementation

The `Collection` class implements the `Collectible` interface and provides a concrete implementation for handling
collections.

It allows for adding, removing, filtering, and sorting elements, as well as transforming them into different formats
like arrays and JSON.

The class is designed to work with generic key-value pairs, ensuring type safety and flexibility for a variety of use
cases.

```php
<?php

declare(strict_types=1);

namespace Example;

use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Order;
use TinyBlocks\Mapper\KeyPreservation;

$collection = Collection::createFrom(elements: [1, 2, 3, 4, 5])
    ->add(6, 7) 
    ->filter(predicates: static fn(int $value): bool => $value > 3) 
    ->sort(order: Order::ASCENDING_VALUE) 
    ->map(transformations: static fn(int $value): int => $value * 2) 
    ->toArray(keyPreservation: KeyPreservation::DISCARD); 

# Output: [8, 10, 12, 14]
```

### Extending Collection

Domain collections should extend the `Collection` class to inherit all collection behavior:

```php
<?php

declare(strict_types=1);

namespace Example;

use TinyBlocks\Collection\Collection;

final class Invoices extends Collection
{
    public function totalAmount(): float
    {
        return $this->reduce(
            accumulator: static fn(float $carry, Invoice $invoice): float => $carry + $invoice->amount,
            initial: 0.0
        );
    }
}
```

### Creating collections

```php
use TinyBlocks\Collection\Collection;

$eager = Collection::createFrom(elements: [1, 2, 3]);

$eagerFromClosure = Collection::createFromClosure(factory: static function (): array {
    return [1, 2, 3];
});

$lazy = Collection::createLazyFrom(elements: [1, 2, 3]);

$lazyFromClosure = Collection::createLazyFromClosure(factory: static function (): iterable {
    yield 1;
    yield 2;
    yield 3;
});
```

<div id='writing'></div>

## Writing

These methods enable adding, removing, and modifying elements in the Collection.

#### Adding elements

- `add`: Returns a new collection with the specified elements appended.

  ```php
  $collection->add(1, 2, 3);
  ```

  ```php
  $collection->add('X', 'Y', 'Z');
  ```

#### Merging collections

- `merge`: Merges the elements of another Collectible into the current Collection.

  ```php
  $collectionA->merge(other: $collectionB);
  ```

#### Removing elements

- `remove`: Returns a new collection with all occurrences of the specified element removed.

  ```php
  $collection->remove(element: 1);
  ```

- `removeAll`: Returns a new collection with elements removed.
  </br></br>
    - **With a predicate**: Removes only the elements that satisfy the given predicate.

      ```php
      $collection->removeAll(predicate: static fn(Amount $amount): bool => $amount->value > 10.0);
      ```

    - **Without a predicate**: Removes all elements from the Collection.

      ```php
      $collection->removeAll();
      ```

<div id='filtering'></div>

### Filtering

These methods enable filtering elements in the Collection based on specific conditions.

#### Filter by predicate

- `filter`: Retains only elements satisfying all given predicates.
  </br></br>

    - **With predicates**: Retains elements that satisfy the provided predicates.

      ```php
      $collection->filter(predicates: static fn(Amount $amount): bool => $amount->value > 100);
      ```

    - **Without predicates**: Removes all falsy values (e.g., `null`, `false`, `0`, `''`, empty arrays).

      ```php
      $collection->filter();
      ```

<div id='ordering'></div>

### Ordering

These methods enable sorting elements in the Collection based on the specified order and optional comparator.

#### Sort by order and custom comparator

- `sort`: Returns a new sorted collection.

  ```
  Order::ASCENDING_KEY: Sorts the collection in ascending order by key.
  Order::DESCENDING_KEY: Sorts the collection in descending order by key.
  Order::ASCENDING_VALUE: Sorts the collection in ascending order by value.
  Order::DESCENDING_VALUE: Sorts the collection in descending order by value.
  ```

  By default, `Order::ASCENDING_KEY` is used.

  ```php
  use TinyBlocks\Collection\Order;
        
  $collection->sort(order: Order::DESCENDING_VALUE);
  ```

  Sort the Collection using a custom comparator to determine how elements should be compared.

  ```php
  use TinyBlocks\Collection\Order;
        
  $collection->sort(
      order: Order::ASCENDING_VALUE,
      comparator: static fn(Amount $first, Amount $second): int => $first->value <=> $second->value
  );
  ``` 

<div id='retrieving'></div>

### Retrieving

These methods allow access to elements within the Collection, such as fetching the first or last element, counting the
elements, or finding elements that match a specific condition.

#### Retrieve count

- `count`: Returns the total number of elements in the Collection.

  ```php
  $collection->count();
  ```

#### Check if empty

- `isEmpty`: Determines whether the collection has no elements.

  ```php
  $collection->isEmpty();
  ```

#### Retrieve by condition

- `findBy`: Finds the first element that satisfies any given predicate, or returns `null` if no predicate matches.
  When called without predicates, it returns `null`.

  ```php
  $collection->findBy(predicates: static fn(CryptoCurrency $crypto): bool => $crypto->symbol === 'ETH');
  ```

#### Retrieve single elements

- `first`: Retrieves the first element from the Collection or returns a default value if the Collection is empty.

  ```php
  $collection->first(defaultValueIfNotFound: 'fallback');
  ```

- `getBy`: Retrieves an element by its zero-based index or returns a default value if the index is out of bounds.

  ```php
  $collection->getBy(index: 0, defaultValueIfNotFound: 'fallback');
  ```

- `last`: Retrieves the last element from the Collection or returns a default value if the Collection is empty.

  ```php
  $collection->last(defaultValueIfNotFound: 'fallback');
  ```

#### Retrieve collection segments

- `slice`: Extracts a contiguous segment of the collection, starting at the specified offset.
  If length is negative, it excludes that many elements from the end.
  If length is not provided or set to -1, it returns all elements from the specified offset to the end.

  ```php
  $collection->slice(offset: 1, length: 2);
  ```

<div id='comparing'></div>

### Comparing

These methods enable comparing collections to check for equality or to verify element membership.

#### Check if collection contains element

- `contains`: Checks if the Collection contains a specific element. Uses strict equality for scalars and loose equality
  for objects.

  ```php
  $collection->contains(element: 5);
  ```

#### Compare collections for equality

- `equals`: Compares the current Collection with another collection for element-wise equality.

  ```php
  $collectionA->equals(other: $collectionB);
  ```

<div id='aggregation'></div>

### Aggregation

These methods perform operations that return a single value based on the Collection's content, such as summing or
combining elements.

- `reduce`: Combines all elements in the Collection into a single value using the provided accumulator function and an
  initial value. This method is helpful for accumulating results, like summing or concatenating values.

  ```php
  $collection->reduce(
      accumulator: static fn(float $carry, float $amount): float => $carry + $amount,
      initial: 0.0
  );
  ```

- `joinToString`: Joins all elements into a string with the given separator.

  ```php
  $collection->joinToString(separator: ', ');
  ```

<div id='transforming'></div>

### Transforming

These methods allow the Collection's elements to be transformed or converted into different formats.

#### Applying actions without modifying elements

- `each`: Executes actions on each element in the Collection without modification.
  This is a terminal operation that does not return the collection. It is useful for performing side effects, such as
  logging or accumulating values.

  ```php
  $collection->each(actions: static fn(Amount $amount): void => $total += $amount->value);
  ```

#### Grouping elements

- `groupBy`: Groups the elements in the Collection based on the provided classifier.

  ```php
  $collection->groupBy(classifier: static fn(Amount $amount): string => $amount->currency->name);
  ```

#### Mapping elements

- `map`: Applies transformations to each element in the Collection and returns a new collection with the transformed
  elements.

  ```php
  $collection->map(transformations: static fn(int $value): int => $value * 2);
  ```

#### Flattening elements

- `flatten`: Flattens nested iterables by exactly one level. Non-iterable elements are yielded as-is.

  ```php
  $collection->flatten();
  ```

#### Convert to array

- `toArray`: Converts the Collection into an array.

  ```
  KeyPreservation::DISCARD: Converts while discarding the keys.
  KeyPreservation::PRESERVE: Converts while preserving the original keys.
  ```

  By default, `KeyPreservation::PRESERVE` is used.

  ```php
  use TinyBlocks\Mapper\KeyPreservation;
  
  $collection->toArray(keyPreservation: KeyPreservation::DISCARD);
  ```

#### Convert to JSON

- `toJson`: Converts the Collection into a JSON string.

  ```
  KeyPreservation::DISCARD: Converts while discarding the keys.
  KeyPreservation::PRESERVE: Converts while preserving the original keys.
  ```

  By default, `KeyPreservation::PRESERVE` is used.

  ```php
  use TinyBlocks\Mapper\KeyPreservation;
  
  $collection->toJson(keyPreservation: KeyPreservation::DISCARD);
  ```

<div id='evaluation-strategies'></div>

## Evaluation strategies

The complexity of every operation in this library is determined by the evaluation strategy chosen at creation time.
Calling `createFrom`, `createFromEmpty`, or `createFromClosure` produces a collection backed by an `EagerPipeline`.
Calling `createLazyFrom`, `createLazyFromEmpty`, or `createLazyFromClosure` produces a collection backed by a
`LazyPipeline`. All subsequent operations on that collection inherit the behavior of the chosen pipeline.

This is analogous to how `java.util.ArrayList` and `java.util.LinkedList` both implement `java.util.List`, but each
operation has different costs depending on which concrete class backs the list.

### Eager pipeline

When the collection is created eagerly, elements are stored in a plain PHP array. This array is the source of truth
for all operations.

**Creation.** Factory methods like `createFrom` call `iterator_to_array` on the input, consuming all elements
immediately. Time: O(n). Space: O(n).

**Transforming operations.** Every call to a transforming method (`add`, `filter`, `map`, `sort`, etc.) calls
`pipe()` internally, which executes `iterator_to_array($operation->apply($this->elements))`. This means the
operation is applied to all elements immediately and the result is stored in a new array. The time cost depends
on the operation (O(n) for filter, O(n log n) for sort), and the space cost is always O(n) because a new array
is allocated.

**Access operations.** Methods like `count`, `first`, `last`, and `getBy` read the internal array directly.
`count` calls PHP's native `count()` on the array. `first` and `last` use `array_key_first` and `array_key_last`.
`getBy` uses `array_key_exists`. All are O(1) time and O(1) space.

**Terminal operations.** Methods like `contains`, `reduce`, `each`, `equals`, and `findBy` iterate over the
collection. Since the elements are already materialized, the iteration itself is O(n). No additional
materialization cost is incurred.

### Lazy pipeline

When the collection is created lazily, nothing is computed at creation time. The source (iterable or closure) is
stored by reference, and operations are accumulated as stages in an array.

**Creation.** Factory methods like `createLazyFrom` store a reference to the iterable. `createLazyFromClosure`
stores the closure without invoking it. Time: O(1). Space: O(1).

**Transforming operations.** Every call to a transforming method calls `pipe()`, which appends the operation to
the internal `$stages` array. No elements are processed. Time: O(1). Space: O(1). The actual cost is deferred
to the moment the collection is consumed.

**Consumption.** When the collection is iterated (explicitly or through `count`, `toArray`, `reduce`, etc.),
`process()` is called. It invokes the source closure (if applicable), then chains all stages into a generator
pipeline. Elements flow one at a time through every stage: each element passes through stage 0, then stage 1,
then stage 2, and so on, before the next element enters the pipeline. For k streaming stages, total time is
O(n * k).

**Access operations.** `count` calls `iterator_count`, which consumes the entire generator: O(n). `first` and
`isEmpty` yield one element from the generator: O(1). `last` and `getBy` iterate the generator: O(n) worst case.

**Barrier operations.** Most operations are streaming: they process one element at a time without accumulating
state. Two operations are exceptions. `sort` must consume all input (via `iterator_to_array`), sort it, then
yield the sorted result: O(n log n) time, O(n) space. `groupBy` must accumulate all elements into a groups
array, then yield: O(n) time, O(n) space. When a barrier exists in a lazy pipeline, it forces full evaluation
of all preceding stages before any subsequent stage can process an element. This means that calling `first()`
on a lazy collection that has a `sort()` in its pipeline still costs O(n log n), because the sort barrier must
consume everything first.

### Complexity reference

The table below summarizes the time and space complexity of each method under both strategies. Each value was
derived by tracing the execution path from `Collection` through the `Pipeline` into the underlying `Operation`.
The column "Why" references the pipeline behavior described above.

#### Factory methods

| Method                  | Time | Space | Why                                                  |
|-------------------------|------|-------|------------------------------------------------------|
| `createFrom`            | O(n) | O(n)  | Calls `iterator_to_array` on the input.              |
| `createFromEmpty`       | O(1) | O(1)  | Creates an empty array.                              |
| `createFromClosure`     | O(n) | O(n)  | Invokes the closure, then calls `iterator_to_array`. |
| `createLazyFrom`        | O(1) | O(1)  | Stores the iterable reference without iterating.     |
| `createLazyFromEmpty`   | O(1) | O(1)  | Stores an empty array reference.                     |
| `createLazyFromClosure` | O(1) | O(1)  | Stores the closure without invoking it.              |

#### Transforming methods

For lazy collections, all transforming methods are O(1) time and O(1) space at call time because `pipe()` only
appends a stage. The cost shown below is for eager collections, where `pipe()` materializes immediately.

| Method      | Time       | Space    | Why                                                                                      |
|-------------|------------|----------|------------------------------------------------------------------------------------------|
| `add`       | O(n + m)   | O(n + m) | Yields all existing elements, then the m new ones.                                       |
| `merge`     | O(n + m)   | O(n + m) | Yields all elements from both collections.                                               |
| `filter`    | O(n)       | O(n)     | Tests each element against the predicate.                                                |
| `map`       | O(n * t)   | O(n)     | Applies t transformations to each element.                                               |
| `flatten`   | O(n + s)   | O(n + s) | Iterates each element; expands nested iterables by one level. s = total nested elements. |
| `remove`    | O(n)       | O(n)     | Tests each element for equality.                                                         |
| `removeAll` | O(n)       | O(n)     | Tests each element against the predicate.                                                |
| `sort`      | O(n log n) | O(n)     | Materializes all elements, sorts via `uasort` or `ksort`, then yields. Barrier.          |
| `slice`     | O(n)       | O(n)     | Iterates up to offset + length elements.                                                 |
| `groupBy`   | O(n)       | O(n)     | Accumulates all elements into a groups array, then yields. Barrier.                      |

#### Access methods

These delegate directly to the pipeline. The cost differs between eager and lazy because eager reads the
internal array, while lazy must evaluate the generator.

| Method    | Eager | Lazy | Why                                                                    |
|-----------|-------|------|------------------------------------------------------------------------|
| `count`   | O(1)  | O(n) | Eager: `count($array)`. Lazy: `iterator_count($generator)`.            |
| `first`   | O(1)  | O(1) | Eager: `array_key_first`. Lazy: first yield from the generator.        |
| `last`    | O(1)  | O(n) | Eager: `array_key_last`. Lazy: iterates all to reach the last element. |
| `getBy`   | O(1)  | O(n) | Eager: `array_key_exists`. Lazy: iterates until the index.             |
| `isEmpty` | O(1)  | O(1) | Checks if the first element exists.                                    |

#### Terminal methods

These iterate the collection to produce a result. Since eager collections already hold a materialized array, the
iteration cost is the same for both strategies.

| Method         | Time     | Space | Why                                                             |
|----------------|----------|-------|-----------------------------------------------------------------|
| `contains`     | O(n)     | O(1)  | Iterates until the element is found or the end is reached.      |
| `findBy`       | O(n * p) | O(1)  | Tests p predicates per element until a match.                   |
| `each`         | O(n * a) | O(1)  | Applies a actions to every element.                             |
| `equals`       | O(n)     | O(1)  | Walks two generators in parallel, comparing element by element. |
| `reduce`       | O(n)     | O(1)  | Folds all elements into a single carry value.                   |
| `joinToString` | O(n)     | O(n)  | Accumulates into an intermediate array, then calls `implode`.   |
| `toArray`      | O(n)     | O(n)  | Iterates all elements into a new array.                         |
| `toJson`       | O(n)     | O(n)  | Calls `toArray`, then `json_encode`.                            |

<div id='faq'></div> 

## FAQ

### 01. Why is my iterator consumed after certain operations?

The `Collection` class leverages [PHP's Generators](https://www.php.net/manual/en/language.generators.overview.php) to
provide lazy evaluation, meaning elements are only generated as needed.

It cannot be reused once a generator is consumed (i.e., after you iterate over it or apply certain operations).

This behavior is intended to optimize memory usage and performance but can sometimes lead to confusion when reusing an
iterator after operations like `count`, `toJson`, or `toArray`.

### 02. How does lazy evaluation affect memory usage in Collection?

Lazy evaluation, enabled by [PHP's Generators](https://www.php.net/manual/en/language.generators.overview.php), allows
`Collection` to handle large datasets without loading all elements into memory at once.

This results in significant memory savings when working with large datasets or performing complex
chained operations.

However, this also means that some operations will consume the generator, and you cannot access the elements unless you
recreate the `Collection`.

### 03. What is the difference between eager and lazy evaluation?

- **Eager evaluation** (`createFrom` / `createFromEmpty` / `createFromClosure`): Elements are materialized immediately
  into an array, enabling constant-time access by index, count, first, last, and repeated iteration.

- **Lazy evaluation** (`createLazyFrom` / `createLazyFromEmpty` / `createLazyFromClosure`): Elements are processed
  on-demand through generators, consuming memory only as each element is yielded. Ideal for large datasets or pipelines
  where not all elements need to be materialized.

<div id='license'></div>

## License

Collection is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
