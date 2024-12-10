<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

/**
 * Defines the possible sorting orders for a Collection.
 */
enum Order
{
    /**
     * Sorts the Collection by the keys in ascending order.
     */
    case ASCENDING_KEY;

    /**
     * Sorts the Collection by the keys in descending order.
     */
    case DESCENDING_KEY;

    /**
     * Sorts the Collection by the values in ascending order.
     */
    case ASCENDING_VALUE;

    /**
     * Sorts the Collection by the values in descending order.
     */
    case DESCENDING_VALUE;
}
