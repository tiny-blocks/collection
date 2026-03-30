<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

/**
 * Defines the sorting strategy applied to a collection.
 *
 * Key-based strategies sort by the element's key (index).
 * Value-based strategies sort by the element's value using
 * a comparator or the spaceship operator as default.
 */
enum Order
{
    /**
     * Sorts elements by key in ascending order.
     */
    case ASCENDING_KEY;

    /**
     * Sorts elements by key in descending order.
     */
    case DESCENDING_KEY;

    /**
     * Sorts elements by value in ascending order.
     */
    case ASCENDING_VALUE;

    /**
     * Sorts elements by value in descending order.
     */
    case DESCENDING_VALUE;
}
