<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Quantity;

use Galaxon\Quantities\Internal\CompoundUnit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests that a Quantity cannot be subverted by mutating its internal CompoundUnit.
 *
 * These tests pin the deep-immutability guarantee: a caller holding a Quantity cannot change its dimension, value,
 * or effective type by reaching through $compoundUnit.
 */
#[CoversClass(Quantity::class)]
#[CoversClass(CompoundUnit::class)]
final class QuantityImmutabilityTest extends TestCase
{
    /**
     * Test that operations on a Quantity's compoundUnit return a fresh instance without affecting the Quantity.
     *
     * Deep immutability of a Quantity depends on CompoundUnit being immutable from the outside: the mutator
     * addUnitTerm is private (pinned by CompoundUnitTest::testAddUnitTermIsPrivate), and arithmetic methods such
     * as mul() return new instances. Together these guarantee that nothing a caller can do through
     * `$q->compoundUnit` can change `$q`.
     */
    public function testQuantityCannotBeSubvertedViaCompoundUnitAccess(): void
    {
        $angle = Angle::create(1, 'rad');

        // Call the most plausible mutation-by-mistake: multiply the compound unit by an unrelated unit.
        $derived = $angle->compoundUnit->mul(CompoundUnit::parse('kg'));

        // The original Angle is untouched — same value, same unit, same dimension, same type.
        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertSame(1.0, $angle->value);
        $this->assertSame('rad', $angle->compoundUnit->asciiSymbol);
        $this->assertSame('A', $angle->compoundUnit->dimension);

        // The derived CompoundUnit is a separate object.
        $this->assertNotSame($angle->compoundUnit, $derived);
        $this->assertInstanceOf(CompoundUnit::class, $derived);
    }

    /**
     * Test that Quantity's compoundUnit reference is readonly — can't be reassigned externally.
     */
    public function testCompoundUnitPropertyIsReadonly(): void
    {
        $length = Length::create(1, 'm');
        $other = CompoundUnit::parse('kg');

        $this->expectException(\Error::class);
        // @phpstan-ignore property.readOnlyAssignNotInConstructor
        $length->compoundUnit = $other;
    }

    /**
     * Test that Quantity's value is readonly — can't be reassigned externally.
     */
    public function testValuePropertyIsReadonly(): void
    {
        $length = Length::create(1, 'm');

        $this->expectException(\Error::class);
        // @phpstan-ignore property.readOnlyAssignNotInConstructor
        $length->value = 999.0;
    }
}
