# Collection

[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/tiny-blocks/collection/blob/main/LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    + [Writing](#writing)
    + [Filtering](#filtering)
    + [Ordering](#ordering)
    + [Retrieving](#retrieving)
    + [Comparing](#comparing)
    + [Aggregation](#aggregation)
    + [Transforming](#transforming)
* [FAQ](#faq)
* [License](#license)
* [Contributing](#contributing)

## Overview

The `Collection` library provides a flexible and efficient API to manipulate, iterate, and manage collections in a
structured and type-safe manner.

It leverages [PHP's Generators](https://www.php.net/manual/en/language.generators.overview.php) for optimized memory
usage and lazy evaluation, ensuring that large datasets are handled efficiently without loading all
elements into memory at once.

The library supports adding, removing, filtering, sorting, and transforming elements.

## Installation

```
composer require tiny-blocks/collection
```

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

### Writing

These methods enable adding, removing, and modifying elements in the Collection.

#### Adding elements

* `add`: Returns a new collection with the specified elements appended.

  ```php
  $collection->add(1, 2, 3);
  ```

  ```php
  $collection->add('X', 'Y', 'Z');
  ```

#### Merging collections

* `merge`: Merges the elements of another Collectible into the current Collection.

  ```php
  $collectionA->merge(other: $collectionB);
  ```

#### Removing elements

* `remove`: Returns a new collection with all occurrences of the specified element removed.

  ```php
  $collection->remove(element: 1);
  ```
* `removeAll`: Returns a new collection with elements removed.

    + **With a predicate**: Removes only the elements that satisfy the given predicate.

      ```php
      $collection->removeAll(predicate: static fn(Amount $amount): bool => $amount->value > 10.0);
      ```
    + **Without a predicate**: Removes all elements from the Collection.

      ```php
      $collection->removeAll();
      ```

### Filtering

These methods enable filtering elements in the Collection based on specific conditions.

#### Filter by predicate

* `filter`: Retains only elements satisfying all given predicates.

    + **With predicates**: Retains elements that satisfy the provided predicates.

      ```php
      $collection->filter(predicates: static fn(Amount $amount): bool => $amount->value > 100);
      ```
    + **Without predicates**: Removes all falsy values (e.g., `null`, `false`, `0`, `''`, empty arrays).

      ```php
      $collection->filter();
      ```

### Ordering

These methods enable sorting elements in the Collection based on the specified order and optional comparator.

#### Sort by order and custom comparator

* `sort`: Returns a new sorted collection.

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

### Retrieving

These methods allow access to elements within the Collection, such as fetching the first or last element, counting the
elements, or finding elements that match a specific condition.

#### Retrieve count

* `count`: Returns the total number of elements in the Collection.

  ```php
  $collection->count();
  ```

#### Check if empty

* `isEmpty`: Determines whether the collection has no elements.

  ```php
  $collection->isEmpty();
  ```

#### Retrieve by condition

* `findBy`: Finds the first element that satisfies any given predicate, or returns `null` if no predicate matches.
  When called without predicates, it returns `null`.

  ```php
  $collection->findBy(predicates: static fn(CryptoCurrency $crypto): bool => $crypto->symbol === 'ETH');
  ```

#### Retrieve single elements

* `first`: Retrieves the first element from the Collection or returns a default value if the Collection is empty.

  ```php
  $collection->first(defaultValueIfNotFound: 'fallback');
  ```
* `getBy`: Retrieves an element by its zero-based index or returns a default value if the index is out of bounds.

  ```php
  $collection->getBy(index: 0, defaultValueIfNotFound: 'fallback');
  ```
* `last`: Retrieves the last element from the Collection or returns a default value if the Collection is empty.

  ```php
  $collection->last(defaultValueIfNotFound: 'fallback');
  ```

#### Retrieve collection segments

* `slice`: Extracts a contiguous segment of the collection, starting at the specified offset.
  If length is negative, it excludes that many elements from the end.
  If length is not provided or set to -1, it returns all elements from the specified offset to the end.

  ```php
  $collection->slice(offset: 1, length: 2);
  ```

### Comparing

These methods enable comparing collections to check for equality or to verify element membership.

#### Check if collection contains element

* `contains`: Checks if the Collection contains a specific element. Uses strict equality for scalars and loose equality
  for objects.

  ```php
  $collection->contains(element: 5);
  ```

#### Compare collections for equality

* `equals`: Compares the current Collection with another collection for element-wise equality.

  ```php
  $collectionA->equals(other: $collectionB);
  ```

### Aggregation

These methods perform operations that return a single value based on the Collection's content, such as summing or
combining elements.

* `reduce`: Combines all elements in the Collection into a single value using the provided accumulator function and an
  initial value. This method is helpful for accumulating results, like summing or concatenating values.

  ```php
  $collection->reduce(
      accumulator: static fn(float $carry, float $amount): float => $carry + $amount,
      initial: 0.0
  );
  ```
* `joinToString`: Joins all elements into a string with the given separator.

  ```php
  $collection->joinToString(separator: ', ');
  ```

### Transforming

These methods allow the Collection's elements to be transformed or converted into different formats.

#### Applying actions without modifying elements

* `each`: Executes actions on each element in the Collection without modification.
  The method is helpful for performing side effects, such as logging or accumulating values.

  ```php
  $collection->each(actions: static fn(Amount $amount): void => $total += $amount->value);
  ```

#### Grouping elements

* `groupBy`: Groups the elements in the Collection based on the provided classifier.

  ```php
  $collection->groupBy(classifier: static fn(Amount $amount): string => $amount->currency->name);
  ```

#### Mapping elements

* `map`: Applies transformations to each element in the Collection and returns a new collection with the transformed
  elements.

  ```php
  $collection->map(transformations: static fn(int $value): int => $value * 2);
  ```

#### Flattening elements

* `flatten`: Flattens nested iterables by exactly one level. Non-iterable elements are yielded as-is.

  ```php
  $collection->flatten();
  ```

#### Convert to array

* `toArray`: Converts the Collection into an array.

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

* `toJson`: Converts the Collection into a JSON string.

  ```
  KeyPreservation::DISCARD: Converts while discarding the keys.
  KeyPreservation::PRESERVE: Converts while preserving the original keys.
  ```

  By default, `KeyPreservation::PRESERVE` is used.

  ```php
  use TinyBlocks\Mapper\KeyPreservation;

  $collection->toJson(keyPreservation: KeyPreservation::DISCARD);
  ```

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

Both modes share the same execution model. Transforming operations append a stage to the pipeline at the call site
without iterating. Terminal operations run the fused pass over all chained stages.

The difference is what each mode does at creation and after the fused pass completes:

* **Eager** (`createFrom*`): the source is materialized into an array at creation. The first terminal call runs the
  fused pass and caches the result. Subsequent terminal calls reuse the cache.
* **Lazy** (`createLazyFrom*`): the source is stored by reference. Every terminal call re-runs the entire pipeline.

**Notation.** `n` = source size at the terminal call. `P` = total cost of the fused pass, equal to the sum of the
per-element contributions of every chained stage. For a pipeline of pure per-element stages, `P` is O(n · s), where
`s` is the number of stages. Non-linear stages (`sort`, `groupBy`) dominate `P`.

#### Creation

| Method                  | Eager                                                             | Lazy                                                           |
|-------------------------|-------------------------------------------------------------------|----------------------------------------------------------------|
| `createFrom`            | O(n) time, O(n) space. Iterates the input once and stores it.     | —                                                              |
| `createFromEmpty`       | O(1) time, O(1) space.                                            | —                                                              |
| `createFromClosure`     | O(n) time, O(n) space. Invokes the factory and stores the result. | —                                                              |
| `createLazyFrom`        | —                                                                 | O(1) time, O(1) space. Stores the iterable by reference.       |
| `createLazyFromEmpty`   | —                                                                 | O(1) time, O(1) space.                                         |
| `createLazyFromClosure` | —                                                                 | O(1) time, O(1) space. Stores the factory without invoking it. |

#### Transforming

Transforming methods append a pipeline stage at the call site and execute only during the fused pass.

| Method      | Call site (both modes) | Contribution to the fused pass                                                           |
|-------------|------------------------|------------------------------------------------------------------------------------------|
| `add`       | O(1) time, O(1) space. | O(m) time, O(m) space, where `m` is the number of appended elements.                     |
| `merge`     | O(1) time, O(1) space. | O(m) time, O(m) space, where `m` is the number of elements in the other collection.      |
| `remove`    | O(1) time, O(1) space. | O(n) time, O(1) space.                                                                   |
| `removeAll` | O(1) time, O(1) space. | O(n) time, O(1) space.                                                                   |
| `filter`    | O(1) time, O(1) space. | O(n · p) time, O(1) space, where `p` is the number of predicates.                        |
| `flatten`   | O(1) time, O(1) space. | O(n + s) time, O(1) space, where `s` is the total number of nested elements.             |
| `map`       | O(1) time, O(1) space. | O(n · t) time, O(1) space, where `t` is the number of transformations.                   |
| `slice`     | O(1) time, O(1) space. | O(min(offset + length, n)) time, O(1) space. Short-circuits once the segment is emitted. |
| `groupBy`   | O(1) time, O(1) space. | O(n) time, O(n) space. Buffers all groups before emitting. Breaks streaming.             |
| `sort`      | O(1) time, O(1) space. | O(n log n) time, O(n) space. Buffers all elements before emitting. Breaks streaming.     |

#### Terminal

Terminal methods trigger the fused pass. Eager cells show **first call / subsequent calls** when they differ.
Subsequent calls read the cache without re-running the pipeline.

| Method         | Eager                                                                                              | Lazy                                                             |
|----------------|----------------------------------------------------------------------------------------------------|------------------------------------------------------------------|
| `count`        | Amortized O(P) / O(1).                                                                             | O(P) per call. Must reach the end.                               |
| `first`        | Amortized O(P) / O(1).                                                                             | O(P_first) per call. Short-circuits at the first element.        |
| `last`         | Amortized O(P) / O(1).                                                                             | O(P) per call. Must reach the end.                               |
| `getBy`        | Amortized O(P) / O(1).                                                                             | O(P_index) per call. Short-circuits at the requested index.      |
| `isEmpty`      | Amortized O(P) / O(1).                                                                             | O(P_first) per call. Short-circuits at the first element.        |
| `contains`     | O(P + n) / O(n). Short-circuits at the first match.                                                | O(P) per call. Short-circuits at the first match.                |
| `findBy`       | O(P + n · p) / O(n · p), where `p` is the number of predicates. Short-circuits at the first match. | O(P + p) per emitted element. Short-circuits at the first match. |
| `each`         | O(P + n · a) / O(n · a), where `a` is the number of actions.                                       | O(P + n · a) per call.                                           |
| `equals`       | O(P + n) / O(n). Short-circuits at the first mismatch.                                             | O(P + n) per call. Short-circuits at the first mismatch.         |
| `joinToString` | O(P + n) / O(n) time, O(n) space.                                                                  | O(P + n) per call.                                               |
| `reduce`       | O(P + n) / O(n) time, O(1) intermediate space.                                                     | O(P + n) per call.                                               |
| `toArray`      | O(P + n) / O(n) time, O(n) space.                                                                  | O(P + n) per call.                                               |
| `toJson`       | O(P + n) / O(n) time, O(n) space.                                                                  | O(P + n) per call.                                               |

Eager aggregation terminals iterate the cached array without re-running the pipeline. Lazy terminals re-run the
pipeline on every call. Eager indexing terminals (`count`, `first`, `last`, `getBy`, `isEmpty`) return in O(1) from
the cache after the first access.

## License

Collection is licensed under [MIT](https://github.com/tiny-blocks/collection/blob/main/LICENSE).

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
