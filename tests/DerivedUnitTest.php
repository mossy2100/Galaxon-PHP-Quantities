<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DerivedUnit class.
 */
#[CoversClass(DerivedUnit::class)]
class DerivedUnitTest extends TestCase
{
    // region Constructor tests

    public function testConstructorWithNull(): void
    {
        $du = new DerivedUnit(null);
        $this->assertEmpty($du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testConstructorWithNoArgument(): void
    {
        $du = new DerivedUnit();
        $this->assertEmpty($du->unitTerms);
    }

    public function testConstructorWithUnit(): void
    {
        $metre = UnitRegistry::getBySymbol('m');
        $du = new DerivedUnit($metre);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m', $du->format(true));
    }

    public function testConstructorWithUnitTerm(): void
    {
        $unitTerm = new UnitTerm('m', 'k', 2);
        $du = new DerivedUnit($unitTerm);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    public function testConstructorWithArrayOfUnitTerms(): void
    {
        // kg is g with prefix k
        $kg = new UnitTerm('g', 'k');
        $m = new UnitTerm('m');
        $s = new UnitTerm('s', null, -2);

        $du = new DerivedUnit([$kg, $m, $s]);

        $this->assertCount(3, $du->unitTerms);
        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m*s-2', $du->format(true));
    }

    // endregion

    // region parse() tests

    public function testParseSimpleUnit(): void
    {
        $du = DerivedUnit::parse('m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m', $du->format(true));
    }

    public function testParseUnitWithPrefix(): void
    {
        $du = DerivedUnit::parse('km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km', $du->format(true));
    }

    public function testParseUnitWithExponent(): void
    {
        $du = DerivedUnit::parse('m2');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testParseCompoundUnitWithAsterisk(): void
    {
        $du = DerivedUnit::parse('kg*m');
        $this->assertCount(2, $du->unitTerms);
        // Sorted by dimension order.
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithMiddot(): void
    {
        $du = DerivedUnit::parse('kg·m');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithDot(): void
    {
        $du = DerivedUnit::parse('kg.m');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithDivision(): void
    {
        $du = DerivedUnit::parse('m/s');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('m*s-1', $du->format(true));
    }

    public function testParseComplexUnit(): void
    {
        $du = DerivedUnit::parse('kg*m/s2');
        $this->assertCount(3, $du->unitTerms);
        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m*s-2', $du->format(true));
    }

    public function testParseNewton(): void
    {
        // Newton is kg⋅m⋅s⁻², dimension MLT-2.
        $du = DerivedUnit::parse('kg*m/s2');
        $this->assertSame('MLT-2', $du->dimension);
    }

    public function testParseWithSuperscriptExponent(): void
    {
        $du = DerivedUnit::parse('m²');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('L2', $du->dimension);
    }

    public function testParseInvalidUnit(): void
    {
        $this->expectException(DomainException::class);
        DerivedUnit::parse('xyz');
    }

    public function testParseMultipleDivisions(): void
    {
        // m/s/s should be m⋅s⁻²
        $du = DerivedUnit::parse('m/s/s');
        $this->assertSame('m*s-2', $du->format(true));
    }

    // endregion

    // region __toString() and format() tests

    public function testToStringEmpty(): void
    {
        $du = new DerivedUnit();
        $this->assertSame('', (string)$du);
    }

    public function testToStringSingleUnit(): void
    {
        $du = DerivedUnit::parse('m');
        $this->assertSame('m', (string)$du);
    }

    public function testToStringCompoundUnit(): void
    {
        $du = DerivedUnit::parse('m*s-2');
        // __toString returns Unicode format with superscript exponents
        $this->assertSame('m⋅s⁻²', (string)$du);
    }

    public function testToStringUsesUnicodeSymbol(): void
    {
        // Ohm has Unicode symbol Ω
        $du = DerivedUnit::parse('ohm');
        $this->assertSame('Ω', (string)$du);
    }

    public function testFormatAsciiEmpty(): void
    {
        $du = new DerivedUnit();
        $this->assertSame('', $du->format(true));
    }

    public function testFormatAsciiSingleUnit(): void
    {
        $du = DerivedUnit::parse('m');
        $this->assertSame('m', $du->format(true));
    }

    public function testFormatAsciiCompoundUnit(): void
    {
        $du = DerivedUnit::parse('m*s-2');
        // format(true) returns ASCII format with '*' separator
        $this->assertSame('m*s-2', $du->format(true));
    }

    public function testFormatAsciiUsesAsciiSymbol(): void
    {
        // Ohm has ASCII symbol 'ohm' and Unicode symbol 'Ω'
        $du = DerivedUnit::parse('ohm');
        $this->assertSame('ohm', $du->format(true));
    }

    public function testFormatUnicodeDefault(): void
    {
        $du = DerivedUnit::parse('m2');
        // format() defaults to Unicode
        $this->assertSame('m²', $du->format());
    }

    public function testFormatUnicodeCompoundUnit(): void
    {
        $du = DerivedUnit::parse('m*s-1');
        $this->assertSame('m⋅s⁻¹', $du->format());
    }

    // endregion

    // region $dimension property tests

    public function testDimensionSimpleUnit(): void
    {
        $du = DerivedUnit::parse('m');
        $this->assertSame('L', $du->dimension);
    }

    public function testDimensionUnitWithExponent(): void
    {
        $du = DerivedUnit::parse('m3');
        $this->assertSame('L3', $du->dimension);
    }

    public function testDimensionCompoundUnit(): void
    {
        $du = DerivedUnit::parse('m/s');
        $this->assertSame('LT-1', $du->dimension);
    }

    public function testDimensionEmptyUnit(): void
    {
        $du = new DerivedUnit();
        $this->assertSame('', $du->dimension);
    }

    public function testDimensionVelocity(): void
    {
        // Velocity: length/time
        $du = DerivedUnit::parse('km/s');
        $this->assertSame('LT-1', $du->dimension);
    }

    public function testDimensionAcceleration(): void
    {
        // Acceleration: length/time²
        $du = DerivedUnit::parse('m/s2');
        $this->assertSame('LT-2', $du->dimension);
    }

    public function testDimensionForce(): void
    {
        // Force (Newton): mass⋅length/time²
        $du = DerivedUnit::parse('kg*m/s2');
        $this->assertSame('MLT-2', $du->dimension);
    }

    public function testDimensionEnergy(): void
    {
        // Energy (Joule): mass⋅length²/time²
        $du = DerivedUnit::parse('kg*m2/s2');
        $this->assertSame('ML2T-2', $du->dimension);
    }

    // endregion

    // region equal() tests

    public function testEqualSameUnits(): void
    {
        $du1 = DerivedUnit::parse('m/s');
        $du2 = DerivedUnit::parse('m/s');

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualCompoundUnits(): void
    {
        $du1 = DerivedUnit::parse('kg*m/s2');
        $du2 = DerivedUnit::parse('kg*m*s-2');

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualDifferentUnits(): void
    {
        $du1 = DerivedUnit::parse('m');
        $du2 = DerivedUnit::parse('km');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualDifferentExponents(): void
    {
        $du1 = DerivedUnit::parse('m2');
        $du2 = DerivedUnit::parse('m3');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualDifferentNumberOfTerms(): void
    {
        $du1 = DerivedUnit::parse('m');
        $du2 = DerivedUnit::parse('m*s');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualEmptyUnits(): void
    {
        $du1 = new DerivedUnit();
        $du2 = new DerivedUnit();

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualWithNonDerivedUnit(): void
    {
        $du = DerivedUnit::parse('m');

        $this->assertFalse($du->equal('m'));
        $this->assertFalse($du->equal(null));
        $this->assertFalse($du->equal(123));
    }

    public function testEqualDifferentPrefixes(): void
    {
        $du1 = DerivedUnit::parse('km');
        $du2 = DerivedUnit::parse('mm');

        $this->assertFalse($du1->equal($du2));
    }

    // endregion

    // region removeUnitTerm() tests

    public function testRemoveUnitTermExisting(): void
    {
        $du = DerivedUnit::parse('kg*m');
        $this->assertCount(2, $du->unitTerms);
        $unitTerm = new UnitTerm('m');
        $du->removeUnitTerm($unitTerm);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('kg', $du->format(true));
    }

    public function testRemoveUnitTermNonExisting(): void
    {
        $du = DerivedUnit::parse('m');
        $this->assertCount(1, $du->unitTerms);
        $unitTerm = new UnitTerm('kg');

        // Should not throw
        $du->removeUnitTerm($unitTerm);

        $this->assertCount(1, $du->unitTerms);
    }

    // endregion

    // region addUnitTerm() tests

    public function testAddUnitTermNew(): void
    {
        $du = new DerivedUnit();
        $unitTerm = new UnitTerm('m');

        $du->addUnitTerm($unitTerm);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m', $du->format(true));
    }

    public function testAddUnitTermCombinesExponents(): void
    {
        $du = DerivedUnit::parse('m2');
        $unitTerm = new UnitTerm('m', null, 3);

        $du->addUnitTerm($unitTerm);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m5', $du->format(true));
    }

    public function testAddUnitTermCombinesWithDifferentExponents(): void
    {
        $du = DerivedUnit::parse('m3');
        $unitTerm = new UnitTerm('m', null, -1);

        $du->addUnitTerm($unitTerm);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testAddUnitTermRemovesWhenZeroExponent(): void
    {
        $du = DerivedUnit::parse('m2');
        $unitTerm = new UnitTerm('m', null, -2);

        $du->addUnitTerm($unitTerm);

        $this->assertCount(0, $du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testAddUnitTermWithPrefix(): void
    {
        $du = DerivedUnit::parse('km2');
        $unitTerm = new UnitTerm('m', 'k', 1);

        $du->addUnitTerm($unitTerm);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km3', $du->format(true));
    }

    public function testAddUnitTermDifferentPrefixesTreatedSeparately(): void
    {
        // km and m should be treated as different unit terms (different keys)
        $du = DerivedUnit::parse('km');
        $unitTerm = new UnitTerm('m');

        $du->addUnitTerm($unitTerm);

        // They have different symbolWithoutExponent ('km' vs 'm'), so they're separate
        $this->assertCount(2, $du->unitTerms);
    }

    // endregion

    // region toDerivedUnit() tests

    public function testToDerivedUnitFromDerivedUnit(): void
    {
        $original = DerivedUnit::parse('kg*m/s2');
        $result = DerivedUnit::toDerivedUnit($original);

        $this->assertSame($original, $result);
    }

    public function testToDerivedUnitFromString(): void
    {
        $result = DerivedUnit::toDerivedUnit('m/s');

        $this->assertInstanceOf(DerivedUnit::class, $result);
        $this->assertSame('m*s-1', $result->format(true));
    }

    public function testToDerivedUnitFromUnit(): void
    {
        $metre = UnitRegistry::getBySymbol('m');
        $result = DerivedUnit::toDerivedUnit($metre);

        $this->assertInstanceOf(DerivedUnit::class, $result);
        $this->assertSame('m', $result->format(true));
    }

    public function testToDerivedUnitFromUnitTerm(): void
    {
        $unitTerm = new UnitTerm('m', 'k', 2);
        $result = DerivedUnit::toDerivedUnit($unitTerm);

        $this->assertInstanceOf(DerivedUnit::class, $result);
        $this->assertSame('km2', $result->format(true));
    }

    public function testToDerivedUnitFromNull(): void
    {
        $result = DerivedUnit::toDerivedUnit(null);

        $this->assertInstanceOf(DerivedUnit::class, $result);
        $this->assertSame('', $result->format(true));
    }

    // endregion

    // region inv() tests

    public function testInvSingleUnit(): void
    {
        $du = DerivedUnit::parse('m');
        $inv = $du->inv();

        $this->assertSame('m-1', $inv->format(true));
        $this->assertSame('L-1', $inv->dimension);
    }

    public function testInvUnitWithExponent(): void
    {
        $du = DerivedUnit::parse('m2');
        $inv = $du->inv();

        $this->assertSame('m-2', $inv->format(true));
    }

    public function testInvCompoundUnit(): void
    {
        $du = DerivedUnit::parse('m/s');
        $inv = $du->inv();

        // m⋅s⁻¹ inverted is m⁻¹⋅s
        $this->assertSame('m-1*s', $inv->format(true));
    }

    public function testInvDoesNotModifyOriginal(): void
    {
        $du = DerivedUnit::parse('m');
        $inv = $du->inv();

        $this->assertSame('m', $du->format(true));
        $this->assertSame('m-1', $inv->format(true));
    }

    public function testInvEmpty(): void
    {
        $du = new DerivedUnit();
        $inv = $du->inv();

        $this->assertSame('', $inv->format(true));
    }

    // endregion

    // region Sorting tests

    public function testSortingByDimensionOrderMassLengthTime(): void
    {
        // Add in reverse dimension order: time, length, mass.
        $s = new UnitTerm('s');
        $m = new UnitTerm('m');
        $kg = new UnitTerm('g', 'k');

        $du = new DerivedUnit([$s, $m, $kg]);

        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m*s', $du->format(true));
    }

    public function testSortingMixedExponents(): void
    {
        // kg⋅m⋅s⁻² - force units.
        $s = new UnitTerm('s', null, -2);
        $m = new UnitTerm('m');
        $kg = new UnitTerm('g', 'k');

        $du = new DerivedUnit([$s, $m, $kg]);

        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m*s-2', $du->format(true));
    }

    // endregion

    // region Edge cases

    public function testCombiningSameUnitsViaMultiplication(): void
    {
        // m * m should give m²
        $du = DerivedUnit::parse('m*m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testCombiningSameUnitsViaDivision(): void
    {
        // m / m should give empty (dimensionless)
        $du = DerivedUnit::parse('m/m');
        $this->assertCount(0, $du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testComplexCombination(): void
    {
        // m³ / m should give m²
        $du = DerivedUnit::parse('m3/m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testCombiningSamePrefixedUnitsViaMultiplication(): void
    {
        // km * km should give km²
        $du = DerivedUnit::parse('km*km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    public function testCombiningSamePrefixedUnitsViaDivision(): void
    {
        // km / km should give empty (dimensionless)
        $du = DerivedUnit::parse('km/km');
        $this->assertCount(0, $du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testComplexPrefixedCombination(): void
    {
        // km³ / km should give km²
        $du = DerivedUnit::parse('km3/km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    // endregion

    // region toSi() tests

    public function testToSiSimpleSiUnit(): void
    {
        // metre is already SI base unit
        $du = DerivedUnit::parse('m');
        $si = $du->toSi();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiPrefixedSiUnit(): void
    {
        // kilometre should convert to metre (SI base)
        $du = DerivedUnit::parse('km');
        $si = $du->toSi();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiNonSiUnit(): void
    {
        // foot should convert to metre
        $du = DerivedUnit::parse('ft');
        $si = $du->toSi();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiUnitWithExponent(): void
    {
        // m² stays as m²
        $du = DerivedUnit::parse('m2');
        $si = $du->toSi();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiNonSiUnitWithExponent(): void
    {
        // ft² should convert to m²
        $du = DerivedUnit::parse('ft2');
        $si = $du->toSi();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiPrefixedUnitWithExponent(): void
    {
        // km² should convert to m²
        $du = DerivedUnit::parse('km2');
        $si = $du->toSi();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiCompoundUnit(): void
    {
        // m/s is already SI
        $du = DerivedUnit::parse('m/s');
        $si = $du->toSi();

        $this->assertSame('m*s-1', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiNonSiCompoundUnit(): void
    {
        // ft/s should convert to m*s⁻¹
        $du = DerivedUnit::parse('ft/s');
        $si = $du->toSi();

        $this->assertSame('m*s-1', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiForceUnit(): void
    {
        // kg*m/s² (force) should stay as kg*m*s⁻²
        $du = DerivedUnit::parse('kg*m/s2');
        $si = $du->toSi();

        $this->assertSame('kg*m*s-2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNonSiForceUnit(): void
    {
        // lb*ft/s² should convert to kg*m*s⁻²
        $du = DerivedUnit::parse('lb*ft/s2');
        $si = $du->toSi();

        $this->assertSame('kg*m*s-2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNamedUnitNewton(): void
    {
        // Newton (N) has dimension MLT-2, should convert to kg*m*s⁻²
        $du = DerivedUnit::parse('N');
        $si = $du->toSi();

        $this->assertSame('kg*m*s-2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNamedUnitJoule(): void
    {
        // Joule (J) has dimension ML²T⁻², should convert to kg*m²*s⁻²
        $du = DerivedUnit::parse('J');
        $si = $du->toSi();

        $this->assertSame('kg*m2*s-2', $si->format(true));
        $this->assertSame('ML2T-2', $si->dimension);
    }

    public function testToSiNamedUnitWatt(): void
    {
        // Watt (W) has dimension ML²T⁻³, should convert to kg*m²*s⁻³
        $du = DerivedUnit::parse('W');
        $si = $du->toSi();

        $this->assertSame('kg*m2*s-3', $si->format(true));
        $this->assertSame('ML2T-3', $si->dimension);
    }

    public function testToSiNamedUnitPascal(): void
    {
        // Pascal (Pa) has dimension ML⁻¹T⁻², should convert to kg*m⁻¹*s⁻²
        $du = DerivedUnit::parse('Pa');
        $si = $du->toSi();

        $this->assertSame('kg*m-1*s-2', $si->format(true));
        $this->assertSame('ML-1T-2', $si->dimension);
    }

    public function testToSiNamedUnitHertz(): void
    {
        // Hertz (Hz) has dimension T⁻¹, should convert to s⁻¹
        $du = DerivedUnit::parse('Hz');
        $si = $du->toSi();

        $this->assertSame('s-1', $si->format(true));
        $this->assertSame('T-1', $si->dimension);
    }

    public function testToSiPrefixedNamedUnit(): void
    {
        // kN (kilonewton) has dimension MLT-2, should convert to kg*m*s⁻²
        $du = DerivedUnit::parse('kN');
        $si = $du->toSi();

        $this->assertSame('kg*m*s-2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiMassUnit(): void
    {
        // gram should convert to kg (SI base unit for mass has 'k' prefix)
        $du = DerivedUnit::parse('g');
        $si = $du->toSi();

        $this->assertSame('kg', $si->format(true));
        $this->assertSame('M', $si->dimension);
    }

    public function testToSiNonSiMassUnit(): void
    {
        // pound should convert to kg
        $du = DerivedUnit::parse('lb');
        $si = $du->toSi();

        $this->assertSame('kg', $si->format(true));
        $this->assertSame('M', $si->dimension);
    }

    public function testToSiEmptyUnit(): void
    {
        // Empty (dimensionless) unit should stay empty
        $du = new DerivedUnit();
        $si = $du->toSi();

        $this->assertSame('', $si->format(true));
        $this->assertSame('', $si->dimension);
    }

    public function testToSiPreservesDimension(): void
    {
        // The dimension should be the same before and after toSi()
        $du = DerivedUnit::parse('ft*lb/s2');
        $si = $du->toSi();

        $this->assertSame($du->dimension, $si->dimension);
    }

    public function testToSiDoesNotModifyOriginal(): void
    {
        $du = DerivedUnit::parse('ft');
        $si = $du->toSi();

        $this->assertSame('ft', $du->format(true));
        $this->assertSame('m', $si->format(true));
    }

    public function testToSiComplexMixedUnit(): void
    {
        // mph (miles per hour) components: mi/h - both non-SI
        // Dimension is LT⁻¹, should become m*s⁻¹
        $du = DerivedUnit::parse('mi/h');
        $si = $du->toSi();

        $this->assertSame('m*s-1', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiEnergyPerTime(): void
    {
        // J/s = W, dimension ML²T⁻³
        $du = DerivedUnit::parse('J/s');
        $si = $du->toSi();

        $this->assertSame('kg*m2*s-3', $si->format(true));
        $this->assertSame('ML2T-3', $si->dimension);
    }

    public function testToSiAcceleration(): void
    {
        // ft/s² should become m*s⁻²
        $du = DerivedUnit::parse('ft/s2');
        $si = $du->toSi();

        $this->assertSame('m*s-2', $si->format(true));
        $this->assertSame('LT-2', $si->dimension);
    }

    // endregion
}
