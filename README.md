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
use TinyBlocks\Collection\Internal\Operations\Order\Order;
use TinyBlocks\Collection\Internal\Operations\Transform\PreserveKeys;

$collection = Collection::createFrom(elements: [1, 2, 3, 4, 5])
    ->add(elements: [6, 7]) 
    ->filter(predicates: fn(int $value): bool => $value > 3) 
    ->sort(order: Order::ASCENDING_VALUE) 
    ->map(transformations: fn(int $value): int => $value * 2) 
    ->toArray(preserveKeys: PreserveKeys::DISCARD); 

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
      $collection->removeAll(filter: fn(Amount $amount): bool => $amount->value > 10.0);
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
      $collection->filter(predicates: fn(Amount $amount): bool => $amount->value > 100);
      ```

    - **Without predicates**: Removes all empty or false values (e.g., `null`, `false`, empty arrays).

      ```php
      $collection->filter();
      ```

<div id='ordering'></div>

### Ordering

These methods enable sorting elements in the Collection based on the specified order and optional predicates.

- `sort`: Sorts the Collection.
  </br></br>

    - **Sort by order**: You can sort the Collection in ascending or descending order based on keys or values.

      ```php
      use TinyBlocks\Collection\Internal\Operations\Order\Order;
  
      $collection->sort(order: Order::ASCENDING_KEY);
      $collection->sort(order: Order::DESCENDING_KEY);
      $collection->sort(order: Order::ASCENDING_VALUE);
      $collection->sort(order: Order::DESCENDING_VALUE);
      ```

    - **Sort by custom predicate**: Sort the Collection using a custom predicate to determine how elements should be
      compared.

      ```php
      $collection->sort(order: Order::ASCENDING_VALUE, predicate: fn(Amount $amount): float => $amount->value);
      ```

<div id='retrieving'></div>

### Retrieving

These methods allow access to elements within the Collection, such as fetching the first or last element or finding
elements that match a specific condition.

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

#### Retrieve by condition

- `findBy`: Finds the first element that matches one or more predicates.

  ```php
  $collection->findBy(predicates: fn(CryptoCurrency $crypto): bool => $crypto->symbol === 'ETH');
  ```

<div id='comparing'></div>

### Comparing

These methods enable comparing collections to check for equality or to apply other comparison logic.

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
  $collection->reduce(aggregator: fn(float $carry, float $amount): float => $carry + $amount, initial: 0.0)
  ```

<div id='transforming'></div>

### Transforming

These methods allow the Collection's elements to be transformed or converted into different formats.

#### Mapping elements

- `map`: Applies transformations to each element in the Collection and returns a new collection with the transformed
  elements.

  ```php
  $collection->map(transformations: fn(int $value): int => $value * 2);
  ```

#### Applying actions without modifying elements

- `each`: Executes actions on each element in the Collection without modification.
  The method is helpful for performing side effects, such as logging or adding elements to another collection.

  ```php
  $collection->each(actions: fn(Invoice $invoice): void => $collectionB->add(elements: new InvoiceSummary(amount: $invoice->amount, customer: $invoice->customer)));
  ```

#### Convert to array

- `toArray`: Converts the Collection into an array.
  </br></br>

    - **With preserving keys**: Converts while keeping the original keys.

      ```php
      $collection->toArray(preserveKeys: PreserveKeys::PRESERVE);
      ```

    - **Without preserving keys**: Converts while discarding the keys.

      ```php
      $collection->toArray(preserveKeys: PreserveKeys::DISCARD);
      ```

#### Convert to JSON

- `toJson`: Converts the Collection into a JSON string.
  </br></br>

    - **With preserving keys**: Converts while keeping the original keys.

      ```php
      $collection->toJson(preserveKeys: PreserveKeys::PRESERVE);
      ```

    - **Without preserving keys**: Converts while discarding the keys.

      ```php
      $collection->toJson(preserveKeys: PreserveKeys::DISCARD);
      ```

<div id='faq'></div> 

## FAQ

### 01. Why is my iterator consumed after certain operations?

The `Collection` class leverages [PHP's Generators](https://www.php.net/manual/en/language.generators.overview.php) to
provide lazy evaluation, meaning elements are only generated as needed.

It cannot be reused once a generator is consumed (i.e., after you iterate over it or apply certain operations).

This behavior is intended to optimize memory usage and performance but can sometimes lead to confusion when reusing an
iterator after operations like `reduce`, `map`, or `filter`.

### 02. Why do operations like reduce or map seem to "consume" my Collection?

Operations like `reduce` and `map`, rely on consuming the collection elements,
using PHP generators for memory efficiency.

Once these operations are performed, the generator is exhausted, meaning you cannot retrieve the elements again unless
you regenerate the Collection.

### 03. How does lazy evaluation affect memory usage in Collection?

Lazy evaluation, enabled by [PHP's Generators](https://www.php.net/manual/en/language.generators.overview.php), allows
Collection to handle large datasets without loading all elements into memory at once.

This results in significant memory savings when working with large datasets or performing complex
chained operations.

However, this also means that some operations will entirely consume the generator, and you won't be
able to reaccess the elements unless you recreate the Collection.

<div id='license'></div>

## License

Collection is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
