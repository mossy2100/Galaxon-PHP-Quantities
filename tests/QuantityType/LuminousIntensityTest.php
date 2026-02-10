<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Quantities\QuantityType\LuminousIntensity;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the LuminousIntensity quantity type.
 */
#[CoversClass(LuminousIntensity::class)]
final class LuminousIntensityTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = LuminousIntensity::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns empty array.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = LuminousIntensity::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    // endregion
}
