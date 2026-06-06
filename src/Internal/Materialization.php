<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use TinyBlocks\Collection\Internal\Operations\Operation;

/**
 * @template TValue
 */
final class Materialization
{
    /**
     * @var array<int|string, TValue>|null
     */
    private ?array $cache = null;

    /**
     * @param array<int|string, TValue> $source
     * @param list<Operation<int|string, mixed>> $stages
     */
    private function __construct(private readonly array $source, private readonly array $stages)
    {
    }

    /**
     * @template TElement
     * @param array<int|string, TElement> $source
     * @param list<Operation<int|string, mixed>> $stages
     * @return Materialization<TElement>
     */
    public static function from(array $source, array $stages): Materialization
    {
        return new Materialization(source: $source, stages: $stages);
    }

    /**
     * @return array<int|string, TValue>
     */
    public function elements(): array
    {
        if (is_null($this->cache)) {
            $elements = $this->source;

            foreach ($this->stages as $stage) {
                $elements = $stage->apply(elements: $elements);
            }

            /** @var array<int|string, TValue> $resolved */
            $resolved = is_array($elements) ? $elements : iterator_to_array($elements);
            $this->cache = $resolved;
        }

        return $this->cache;
    }

    /**
     * @param Operation<int|string, mixed> $operation
     * @return Materialization<TValue>
     */
    public function withStage(Operation $operation): Materialization
    {
        return is_null($this->cache)
            ? Materialization::from(source: $this->source, stages: [...$this->stages, $operation])
            : Materialization::from(source: $this->cache, stages: [$operation]);
    }
}
