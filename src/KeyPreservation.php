<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

/**
 * Defines whether array keys are preserved or discarded when materializing a Collectible.
 */
enum KeyPreservation
{
    case DISCARD;
    case PRESERVE;
}
