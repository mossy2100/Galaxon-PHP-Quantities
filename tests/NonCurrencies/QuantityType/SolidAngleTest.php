<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\Tests\NonCurrencies\QuantityType;

use OceanMoon\Quantities\QuantityType\SolidAngle;
use OceanMoon\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the SolidAngle quantity type.
 */
#[CoversClass(SolidAngle::class)]
final class SolidAngleTest extends TestCase
{
    use ArrayShapeTrait;

    #region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = SolidAngle::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = SolidAngle::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    #endregion
}
