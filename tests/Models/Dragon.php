<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Models;

final readonly class Dragon
{
    public function __construct(public string $name, public string $description)
    {
    }
}
