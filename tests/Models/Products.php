<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

final class Products implements IteratorAggregate
{
    private array $products;

    public function __construct(public iterable $elements = [])
    {
        $this->products = is_array($elements) ? $elements : iterator_to_array($elements);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->products);
    }
}
