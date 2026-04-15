<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class EagerPipeline implements Pipeline
{
    private function __construct(private array $elements)
    {
    }

    public static function from(iterable $source): EagerPipeline
    {
        return new EagerPipeline(elements: is_array($source) ? $source : iterator_to_array($source));
    }

    public function pipe(Operation $operation): Pipeline
    {
        $elements = $operation->apply(elements: $this->elements);

        return new EagerPipeline(
            elements: is_array($elements) ? $elements : iterator_to_array($elements)
        );
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        return array_key_exists($index, $this->elements)
            ? $this->elements[$index]
            : $defaultValueIfNotFound;
    }

    public function process(): Generator
    {
        yield from $this->elements;
    }
}
<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class EagerPipeline implements Pipeline
    {
            private function __construct(private array $elements)
        {
        }

    public static function from(iterable $source): EagerPipeline
        {
                    return new EagerPipeline(elements: is_array($source) ? $source : iterator_to_array($source));
        }

    public function pipe(Operation $operation): Pipeline
        {
                    $elements = $operation->apply(elements: $this->elements);

                return new EagerPipeline(
                                elements: is_array($elements) ? $elements : iterator_to_array($elements)
                            );
        }

    public function count(): int
        {
                    return count($this->elements);
        }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
        {
                    return array_key_exists($index, $this->elements)
                                    ? $this->elements[$index]
                                    : $defaultValueIfNotFound;
        }

    public function process(): Generator
        {
                    yield from $this->elements;
        }
    }
