<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class JoinToString implements ImmediateOperation
{
    private function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): JoinToString
    {
        return new JoinToString(elements: $elements);
    }

    public function joinTo(string $separator): string
    {
        $result = '';
        $first = true;

        foreach ($this->elements as $element) {
            if ($first) {
                $result = $element;
                $first = false;
                continue;
            }

            $result .= sprintf('%s%s', $separator, $element);
        }

        return $result;
    }
}
