<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

/**
* Measurement system classification for units.
*/
enum PrefixGroup
{
    case Metric;

    case Binary;

    case Small;

    case Large;

    case Engineering;
}
