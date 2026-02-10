<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Quantities\QuantityType\Density;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Density quantity type.
 */
#[CoversClass(Density::class)]
final class DensityTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns empty array (not overridden).
     */
    public function testGetUnitDefinitionsReturnsEmptyArray(): void
    {
        $units = Density::getUnitDefinitions();

        $this->assertEmpty($units);
    }

    /**
     * Test getConversionDefinitions() returns empty array (not overridden).
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = Density::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    // endregion
}
