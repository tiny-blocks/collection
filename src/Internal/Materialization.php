<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use TinyBlocks\Collection\Internal\Operations\Operation;

final class Materialization
{
    private ?array $cache = null;

    private function __construct(private readonly array $source, private readonly array $stages)
    {
    }

    public static function from(array $source, array $stages): Materialization
    {
        return new Materialization(source: $source, stages: $stages);
    }

    public function elements(): array
    {
        if (is_null($this->cache)) {
            $elements = $this->source;

            foreach ($this->stages as $stage) {
                /** @var Operation $stage */
                $elements = $stage->apply(elements: $elements);
            }

            $this->cache = is_array($elements) ? $elements : iterator_to_array($elements);
        }

        return $this->cache;
    }
}
