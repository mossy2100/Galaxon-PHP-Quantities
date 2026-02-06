<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Quantity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Quantity::convert() static method.
 */
#[CoversClass(Quantity::class)]
final class QuantityConvertTest extends TestCase
{
    /**
     * Test basic unit conversion.
     */
    public function testConvertBasicUnits(): void
    {
        $result = Quantity::convert(60, 'min', 'h');

        $this->assertSame(1.0, $result);
    }

    /**
     * Test conversion with SI prefixes.
     */
    public function testConvertWithPrefixes(): void
    {
        $result = Quantity::convert(1, 'km', 'm');

        $this->assertSame(1000.0, $result);
    }

    /**
     * Test conversion between different unit types.
     */
    public function testConvertBetweenUnitTypes(): void
    {
        // 1 calorie = 4.184 joules
        $result = Quantity::convert(1, 'cal', 'J');

        $this->assertSame(4.184, $result);
    }

    /**
     * Test conversion of derived units.
     */
    public function testConvertDerivedUnits(): void
    {
        // 1 m/s = 3.6 km/h
        $result = Quantity::convert(1, 'm/s', 'km/h');

        $this->assertSame(3.6, $result);
    }

    /**
     * Test identity conversion returns same value.
     */
    public function testConvertIdentity(): void
    {
        $result = Quantity::convert(42.5, 'm', 'm');

        $this->assertSame(42.5, $result);
    }

    /**
     * Test conversion accepts DerivedUnit objects.
     */
    public function testConvertAcceptsDerivedUnitObjects(): void
    {
        $srcUnit = DerivedUnit::parse('kg');
        $destUnit = DerivedUnit::parse('g');

        $result = Quantity::convert(1, $srcUnit, $destUnit);

        $this->assertSame(1000.0, $result);
    }

    /**
     * Test conversion throws for incompatible dimensions.
     */
    public function testConvertThrowsForIncompatibleDimensions(): void
    {
        $this->expectException(DomainException::class);

        Quantity::convert(1, 'm', 's');
    }
}
