<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

/**
 * Measurement system classification for units.
 */
enum System
{
    case SI;

    case SIAccepted;

    case Common;

    case US;

    case Imperial;

    case Scientific;

    case Astronomical;

    case Nautical;

    case Typography;
}
