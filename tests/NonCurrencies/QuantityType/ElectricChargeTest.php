<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\Tests\NonCurrencies\QuantityType;

use OceanMoon\Quantities\QuantityType\ElectricCharge;
use OceanMoon\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the ElectricCharge quantity type.
 */
#[CoversClass(ElectricCharge::class)]
final class ElectricChargeTest extends TestCase
{
    use ArrayShapeTrait;

    #region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = ElectricCharge::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = ElectricCharge::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    #endregion
}
