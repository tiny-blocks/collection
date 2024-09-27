<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Order;

enum Order
{
    case ASCENDING_KEY;
    case DESCENDING_KEY;
    case ASCENDING_VALUE;
    case DESCENDING_VALUE;
}
