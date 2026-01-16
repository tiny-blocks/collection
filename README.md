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
    ->add(elements: [6, 7]) 
    ->filter(predicates: static fn(int $value): bool => $value > 3) 
    ->sort(order: Order::ASCENDING_VALUE) 
    ->map(transformations: static fn(int $value): int => $value * 2) 
    ->toArray(keyPreservation: KeyPreservation::DISCARD); 

# Output: [8, 10, 12, 14]
```

<div id='writing'></div>

### Writing

These methods enable adding, removing, and modifying elements in the Collection.

#### Adding elements

- `add`: Adds one or more elements to the Collection.

  ```php
  $collection->add(elements: [1, 2, 3]);
  ```

  ```php
  $collection ->add('X', 'Y', 'Z');
  ```

#### Removing elements

- `remove`: Removes a specific element from the Collection.

  ```php
  $collection->remove(element: 1);
  ```

- `removeAll`: Removes elements from the Collection.
  </br></br>
    - **With a filter**: Removes only the elements that match the provided filter.

      ```php
      $collection->removeAll(filter: static fn(Amount $amount): bool => $amount->value > 10.0);
      ```

    - **Without a filter**: Removes all elements from the Collection.

      ```php
      $collection->removeAll();
      ```

<div id='ordering'></div>

### Filtering

These methods enable filtering elements in the Collection based on specific conditions.

#### Filter by predicate

- `filter`: Filters elements in the Collection.
  </br></br>

    - **With predicates**: Filter elements are based on the provided predicates.

      ```php
      $collection->filter(predicates: static fn(Amount $amount): bool => $amount->value > 100);
      ```

    - **Without predicates**: Removes all empty or false values (e.g., `null`, `false`, empty arrays).

      ```php
      $collection->filter();
      ```

<div id='ordering'></div>

### Ordering

These methods enable sorting elements in the Collection based on the specified order and optional predicates.

#### Sort by order and custom predicate

- `sort`: Sorts the Collection.

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

  Sort the Collection using a custom predicate to determine how elements should be
  compared.

  ```php
  use TinyBlocks\Collection\Order;
        
  $collection->sort(order: Order::ASCENDING_VALUE, predicate: static fn(Amount $amount): float => $amount->value);
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

#### Retrieve by condition

- `findBy`: Finds the first element that matches one or more predicates.

  ```php
  $collection->findBy(predicates: static fn(CryptoCurrency $crypto): bool => $crypto->symbol === 'ETH');
  ```

<div id='comparing'></div>

#### Retrieve single elements

- `first`: Retrieves the first element from the Collection or returns a default value if the Collection is empty.

  ```php
  $collection->first(defaultValueIfNotFound: 'default');
  ```

- `getBy`: Retrieves an element by its index or returns a default value if the index is out of range.

  ```php
  $collection->getBy(index: 0, defaultValueIfNotFound: 'default');
  ```

- `last`: Retrieves the last element from the Collection or returns a default value if the Collection is empty.

  ```php
  $collection->last(defaultValueIfNotFound: 'default');
  ```

#### Retrieve collection elements

- `slice`: Extracts a portion of the collection, starting at the specified index and retrieving the specified number of
  elements.
  If length is negative, it excludes many elements from the end of the collection.
  If length is not provided or set to -1, it returns all elements from the specified index to the end of the collection.

  ```php
  $collection->slice(index: 1, length: 2);
  ```

### Comparing

These methods enable comparing collections to check for equality or to apply other comparison logic.

#### Check if collection contains element

- `contains`: Checks if the Collection contains a specific element.

  ```php
  $collection->contains(element: 5);
  ```

#### Compare collections for equality

- `equals`: Compares the current Collection with another collection to check if they are equal.

  ```php
  $collectionA->equals(other: $collectionB);
  ```

<div id='aggregation'></div>

### Aggregation

These methods perform operations that return a single value based on the Collection's content, such as summing or
combining elements.

- `reduce`: Combines all elements in the Collection into a single value using the provided aggregator function and an
  initial value.
  This method is helpful for accumulating results, like summing or concatenating values.

  ```php
  $collection->reduce(aggregator: static fn(float $carry, float $amount): float => $carry + $amount, initial: 0.0)
  ```

<div id='transforming'></div>

### Transforming

These methods allow the Collection's elements to be transformed or converted into different formats.

#### Applying actions without modifying elements

- `each`: Executes actions on each element in the Collection without modification.
  The method is helpful for performing side effects, such as logging or adding elements to another collection.

  ```php
  $collection->each(actions: static fn(Invoice $invoice): void => $collectionB->add(elements: new InvoiceSummary(amount: $invoice->amount, customer: $invoice->customer)));
  ```

#### Grouping elements

- `groupBy`: Groups the elements in the Collection based on the provided grouping criterion.

  ```php
  $collection->groupBy(grouping: static fn(Amount $amount): string => $amount->currency->name);
  ```

#### Mapping elements

- `map`: Applies transformations to each element in the Collection and returns a new collection with the transformed
  elements.

  ```php
  $collection->map(transformations: static fn(int $value): int => $value * 2);
  ```

#### Flattening elements

- `flatten`: Flattens a collection by removing any nested collections and returning a single collection with all
  elements in a single level.

  This method recursively flattens any iterable elements, combining them into one collection, regardless of their
  nesting depth.

  ```php
  $collection->flatten();
  ```

#### Convert to array

- `toArray`: Converts the Collection into an array.

  ```
  PreserveKeys::DISCARD: Converts while discarding the keys.
  PreserveKeys::PRESERVE: Converts while preserving the original keys.
  ```

  By default, `PreserveKeys::PRESERVE` is used.

  ```php
  use TinyBlocks\Mapper\KeyPreservation;
  
  $collection->toArray(preserveKeys: KeyPreservation::DISCARD);
  ```

#### Convert to JSON

- `toJson`: Converts the Collection into a JSON string.

  ```
  PreserveKeys::DISCARD: Converts while discarding the keys.
  PreserveKeys::PRESERVE: Converts while preserving the original keys.
  ```

  By default, `PreserveKeys::PRESERVE` is used.

  ```php
  use TinyBlocks\Mapper\KeyPreservation;
  
  $collection->toJson(preserveKeys: KeyPreservation::DISCARD);
  ```

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

<div id='license'></div>

## License

Collection is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
