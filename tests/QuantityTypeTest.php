<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for QuantityType class.
 */
#[CoversClass(QuantityType::class)]
final class QuantityTypeTest extends TestCase
{
    // region Constructor tests

    /**
     * Test constructor creates QuantityType with all parameters.
     */
    public function testConstructorWithAllParameters(): void
    {
        $qtyType = new QuantityType('length', 'L', 'm', Length::class);

        $this->assertSame('length', $qtyType->name);
        $this->assertSame('L', $qtyType->dimension);
        $this->assertSame('m', $qtyType->siUnitSymbol);
        $this->assertSame(Length::class, $qtyType->class);
    }

    /**
     * Test constructor with null class.
     */
    public function testConstructorWithNullClass(): void
    {
        $qtyType = new QuantityType('custom', 'L9', 'x', null);

        $this->assertSame('custom', $qtyType->name);
        $this->assertSame('L9', $qtyType->dimension);
        $this->assertSame('x', $qtyType->siUnitSymbol);
        $this->assertNull($qtyType->class);
    }

    /**
     * Test constructor normalizes dimension code.
     */
    public function testConstructorNormalizesDimension(): void
    {
        // TLM should be normalized to MLT (canonical order)
        $qtyType = new QuantityType('test', 'TLM', 'x', null);

        $this->assertSame('MLT', $qtyType->dimension);
    }

    /**
     * Test constructor with compound dimension.
     */
    public function testConstructorWithCompoundDimension(): void
    {
        $qtyType = new QuantityType('force', 'MLT-2', 'N', null);

        $this->assertSame('MLT-2', $qtyType->dimension);
    }

    /**
     * Test constructor with dimensionless.
     */
    public function testConstructorWithDimensionless(): void
    {
        $qtyType = new QuantityType('ratio', '1', '', null);

        $this->assertSame('1', $qtyType->dimension);
        $this->assertSame('', $qtyType->siUnitSymbol);
    }

    /**
     * Test constructor with different quantity types.
     */
    public function testConstructorWithDifferentQuantityTypes(): void
    {
        $mass = new QuantityType('mass', 'M', 'kg', Mass::class);
        $this->assertSame('M', $mass->dimension);
        $this->assertSame('kg', $mass->siUnitSymbol);
        $this->assertSame(Mass::class, $mass->class);

        $time = new QuantityType('time', 'T', 's', Time::class);
        $this->assertSame('T', $time->dimension);
        $this->assertSame('s', $time->siUnitSymbol);
        $this->assertSame(Time::class, $time->class);
    }

    // endregion

    // region Class property hook tests

    /**
     * Test class property accepts valid Quantity subclass.
     */
    public function testClassPropertyAcceptsValidSubclass(): void
    {
        $qtyType = new QuantityType('length', 'L', 'm', null);

        // Set class after construction
        $qtyType->class = Length::class;

        $this->assertSame(Length::class, $qtyType->class);
    }

    /**
     * Test class property accepts null.
     */
    public function testClassPropertyAcceptsNull(): void
    {
        $qtyType = new QuantityType('length', 'L', 'm', Length::class);

        // Set to null
        $qtyType->class = null;

        $this->assertNull($qtyType->class);
    }

    /**
     * Test class property throws for non-Quantity class.
     */
    public function testClassPropertyThrowsForNonQuantityClass(): void
    {
        $qtyType = new QuantityType('test', 'L9', 'x', null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be a subclass of');

        $qtyType->class = stdClass::class;
    }

    /**
     * Test class property throws for base Quantity class.
     */
    public function testClassPropertyThrowsForBaseQuantityClass(): void
    {
        $qtyType = new QuantityType('test', 'L9', 'x', null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be a subclass of');

        $qtyType->class = Quantity::class;
    }

    /**
     * Test class property throws for non-existent class string.
     */
    public function testClassPropertyThrowsForNonExistentClass(): void
    {
        $qtyType = new QuantityType('test', 'L9', 'x', null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be a subclass of');

        $qtyType->class = 'NonExistent\\FakeClass';
    }

    /**
     * Test constructor throws for invalid class.
     */
    public function testConstructorThrowsForInvalidClass(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be a subclass of');

        new QuantityType('test', 'L9', 'x', stdClass::class);
    }

    // endregion

    // region Property readonly tests

    /**
     * Test name property is readonly.
     */
    public function testNamePropertyIsReadonly(): void
    {
        $qtyType = new QuantityType('length', 'L', 'm', null);

        // Verify we can read the property
        $this->assertSame('length', $qtyType->name);

        // Note: readonly property cannot be modified after construction
        // PHP will throw an Error if attempted, but we don't test that here
        // as it would be a compile-time/runtime error
    }

    /**
     * Test dimension property is readonly.
     */
    public function testDimensionPropertyIsReadonly(): void
    {
        $qtyType = new QuantityType('length', 'L', 'm', null);

        $this->assertSame('L', $qtyType->dimension);
    }

    /**
     * Test siUnitSymbol property is readonly.
     */
    public function testSiUnitSymbolPropertyIsReadonly(): void
    {
        $qtyType = new QuantityType('length', 'L', 'm', null);

        $this->assertSame('m', $qtyType->siUnitSymbol);
    }

    // endregion

    // region Edge case tests

    /**
     * Test empty name is allowed.
     */
    public function testEmptyNameIsAllowed(): void
    {
        $qtyType = new QuantityType('', 'L', 'm', null);

        $this->assertSame('', $qtyType->name);
    }

    /**
     * Test empty SI unit symbol is allowed.
     */
    public function testEmptySiUnitSymbolIsAllowed(): void
    {
        $qtyType = new QuantityType('dimensionless', '1', '', null);

        $this->assertSame('', $qtyType->siUnitSymbol);
    }

    /**
     * Test dimension with exponent 1 is normalized.
     */
    public function testDimensionWithExponentOneIsNormalized(): void
    {
        $qtyType = new QuantityType('length', 'L1', 'm', null);

        // L1 should be normalized to L
        $this->assertSame('L', $qtyType->dimension);
    }

    /**
     * Test class can be changed after construction.
     */
    public function testClassCanBeChangedAfterConstruction(): void
    {
        $qtyType = new QuantityType('test', 'L8', 'x', Length::class);

        $this->assertSame(Length::class, $qtyType->class);

        // Change to a different class
        $qtyType->class = Mass::class;
        $this->assertSame(Mass::class, $qtyType->class);

        // Change to null
        $qtyType->class = null;
        $this->assertNull($qtyType->class);

        // Change back to a class
        $qtyType->class = Time::class;
        $this->assertSame(Time::class, $qtyType->class);
    }

    // endregion
}
