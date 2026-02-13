<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

/**
 * Measurement system classification for units.
 */
enum System
{
    case Si;

    case SiAccepted;

    case Common;

    case Imperial;

    case UsCustomary;

    case Scientific;

    case Astronomical;

    case Nautical;

    case Typographical;
}
