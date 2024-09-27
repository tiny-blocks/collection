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
    * [Transforming](#transforming)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div> 

## Overview

The `Collection` library provides a flexible and efficient API to manipulate, iterate, and manage collections in a
structured and type-safe manner.
It leverages PHP's [Generators](https://www.php.net/manual/en/language.generators.overview.php) for optimized memory
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
use TinyBlocks\Collection\Internal\Operations\Order\Order;
use TinyBlocks\Collection\Internal\Operations\Transform\PreserveKeys;

$collection = Collection::createFrom(elements: [1, 2, 3, 4, 5])
    ->add(6, 7) 
    ->filter(fn(int $value): bool => $value > 3) 
    ->sort(order: Order::ASCENDING_VALUE) 
    ->map(fn(int $value): int => $value * 2) 
    ->toArray(preserveKeys: PreserveKeys::DISCARD); 

# Output: [8, 10, 12, 14]
```

<div id='writing'></div>

### Writing

These methods enable adding, removing, and modifying elements in the collection.

#### Adding elements

- `add`:
  Adds one or more elements to the collection.

  ```php
  $collection->add(elements: [1, 2, 3]);
  ```

  ```php
  $collection->add('X', 'Y', 'Z');
  ```

#### Removing elements

- `remove`: Removes a specific element from the collection.

  ```php
  $collection->remove(element: 1);
  ```

- `removeAll`: Removes elements from the collection based on the provided filter.
  If no filter is passed, all elements in the collection will be removed.

  ```php
  $collection->removeAll(filter: fn(Amount $amount): bool => $amount->value > 10.0);
  ```

  ```php
  $collection->removeAll();
  ```

<div id='ordering'></div>

### Filtering

These methods enable filtering elements in the collection based on specific conditions.

#### Filter by predicate

- `filter`: Filters elements in the collection based on the provided predicates.
  If no predicates are provided, all empty or falsy values (e.g., null, false, empty arrays) will be removed.

  ```php
  $collection->filter(fn(Amount $amount): bool => $amount->value > 100);
  ```

  ```php
  $collection->filter();
  ```

<div id='comparing'></div>

### Ordering

These methods enable sorting elements in the collection based on the specified order and optional predicates.

- `sort`: Sorts the collection based on the provided order and optional predicate.
  You can sort elements in ascending or descending order based on keys or values.
  Optionally, you can provide a predicate to determine how the elements should be compared.

  **Sorts the collection based on the provided order**.

  ```php
  use TinyBlocks\Collection\Internal\Operations\Order\Order;

  $collection->sort(order: Order::ASCENDING_KEY);
  $collection->sort(order: Order::DESCENDING_KEY);
  $collection->sort(order: Order::ASCENDING_VALUE);
  $collection->sort(order: Order::DESCENDING_VALUE);
  ```

  **Sorts the collection using a custom predicate to define how elements should be compared**.

  ```php
  $collection->sort(order: Order::ASCENDING_VALUE, predicate: fn(Amount $amount): float => $amount->value);
  ```

<div id='filtering'></div>

### Retrieving

These methods allow access to elements within the collection, such as fetching the first or last element, or finding
elements that match a specific condition.

#### Retrieve single elements

- `first`: Retrieves the first element from the collection, or returns a default value if the collection is empty.

  ```php
  $collection->first(defaultValueIfNotFound: 'default');
  ```

- `getBy`: Retrieves an element by its index, or returns a default value if the index is out of range.

  ```php
  $collection->getBy(index: 0, defaultValueIfNotFound: 'default');
  ```

- `last`: Retrieves the last element from the collection, or returns a default value if the collection is empty.

  ```php
  $collection->last(defaultValueIfNotFound: 'default');
  ```

#### Retrieve by condition

- `findBy`: Finds the first element that matches one or more predicates.

  ```php
  $collection->findBy(fn(CryptoCurrency $crypto): bool => $crypto->symbol === 'BTC');
  ```

<div id='transforming'></div>

### Comparing

These methods enable comparing collections to check for equality or to apply other comparison logic.

#### Compare collections for equality

- `equals`: Compares the current collection with another collection to check if they are equal.

  ```php
  $isEqual = $collectionA->equals(other: $collectionB);
  ```

<div id='retrieving'></div>

### Transforming

These methods allow the collection's elements to be transformed or converted into different formats.

#### Mapping elements

- `map`: Applies transformations to each element in the collection and returns a new collection with the transformed
  elements.

  ```php
  $collection->map(fn(int $value): int => $value * 2);
  ```

#### Applying actions without modifying elements

- `each`: Executes actions on each element in the collection without modifying it.
  The method is useful for performing side effects, such as logging or adding elements to another collection.

  ```php
  $collection->each(fn(Invoice $invoice): void => $collectionB->add(new InvoiceSummary($invoice->id, $invoice->amount)));
  ```

#### Convert to array

- `toArray`: Converts the collection into an array.

  ```php
  $collection->toArray();
  ```

#### Convert to JSON

- `toJson`: Converts the collection into a JSON string.

  ```php
  $collection->toJson();
  ```

<div id='license'></div>

## License

Collection is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
