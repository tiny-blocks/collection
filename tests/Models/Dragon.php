<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Models;

final readonly class Dragon
{
    public function __construct(public string $name, public string $description)
    {
    }
}
