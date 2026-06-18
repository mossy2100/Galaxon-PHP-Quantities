<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\Tests\NonCurrencies\QuantityType;

use OceanMoon\Quantities\QuantityType\Acceleration;
use OceanMoon\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Acceleration quantity type.
 */
#[CoversClass(Acceleration::class)]
final class AccelerationTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns empty array (not overridden).
     */
    public function testGetUnitDefinitionsReturnsEmptyArray(): void
    {
        $units = Acceleration::getUnitDefinitions();

        $this->assertEmpty($units);
    }

    /**
     * Test getConversionDefinitions() returns empty array (not overridden).
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = Acceleration::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    // endregion
}
