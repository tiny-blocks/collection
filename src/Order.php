<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

/**
 * Defines the ordering applied when sorting a Collectible by key or value.
 */
enum Order
{
    case ASCENDING_KEY;
    case DESCENDING_KEY;
    case ASCENDING_VALUE;
    case DESCENDING_VALUE;
}
