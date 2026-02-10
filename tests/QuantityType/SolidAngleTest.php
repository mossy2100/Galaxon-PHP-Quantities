<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Quantities\QuantityType\SolidAngle;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the SolidAngle quantity type.
 */
#[CoversClass(SolidAngle::class)]
final class SolidAngleTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = SolidAngle::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns empty array.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = SolidAngle::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    // endregion
}
