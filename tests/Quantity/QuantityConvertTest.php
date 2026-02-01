<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use Galaxon\Quantities\Quantity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the base Quantity class.
 */
#[CoversClass(Quantity::class)]
final class QuantityConvertTest extends TestCase
{
    // region Generic Quantity convert tests

    /**
     * Test static convert method on Quantity.
     */
    public function testGenericStaticConvertMethod(): void
    {
        $value = Quantity::convert(60, 'min', 'h');

        $this->assertSame(1.0, $value);
    }

    // endregion
}
