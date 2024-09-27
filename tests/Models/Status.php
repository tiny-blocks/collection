<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

enum Status: int
{
    case PAID = 1;
    case PENDING = 0;
}

