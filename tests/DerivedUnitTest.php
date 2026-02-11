<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use Galaxon\Quantities\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DerivedUnit class.
 */
#[CoversClass(DerivedUnit::class)]
class DerivedUnitTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
    }

    // endregion

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
        $this->assertSame('kg*m/s2', $du->format(true));
    }

    // endregion

    // region parse() tests

    public function testParseEmptyStringReturnsDimensionless(): void
    {
        $du = DerivedUnit::parse('');
        $this->assertEmpty($du->unitTerms);
        $this->assertSame('1', $du->dimension);
        $this->assertSame('', $du->format(true));
    }

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
        $this->assertSame('m/s', $du->format(true));
    }

    public function testParseComplexUnit(): void
    {
        $du = DerivedUnit::parse('kg*m/s2');
        $this->assertCount(3, $du->unitTerms);
        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m/s2', $du->format(true));
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
        $this->assertSame('m/s2', $du->format(true));
    }

    public function testParseParenthesesMultipleTermsInDenominator(): void
    {
        // J/(mol*K) - energy per amount per temperature
        $du = DerivedUnit::parse('J/(mol*K)');
        $this->assertSame('J/(mol*K)', $du->format(true));
        $this->assertSame('ML2T-2N-1H-1', $du->dimension);
    }

    public function testParseParenthesesSingleTermInDenominator(): void
    {
        // m/(s) - single term in parentheses should work
        $du = DerivedUnit::parse('m/(s)');
        $this->assertSame('m/s', $du->format(true));
    }

    public function testParseParenthesesMultipleTermsInBoth(): void
    {
        // kg*m/(s2*A) - multiple terms in numerator and denominator
        $du = DerivedUnit::parse('kg*m/(s2*A)');
        $this->assertSame('kg*m/(s2*A)', $du->format(true));
    }

    public function testParseParenthesesWithMiddleDot(): void
    {
        // J/(mol·K) - using middle dot separator
        $du = DerivedUnit::parse('J/(mol·K)');
        $this->assertSame('J/(mol*K)', $du->format(true));
    }

    public function testParseParenthesesInNumeratorIsInvalid(): void
    {
        // (kg*m)/s - parentheses in numerator not allowed
        $this->expectException(FormatException::class);
        DerivedUnit::parse('(kg*m)/s');
    }

    public function testParseNestedParenthesesIsInvalid(): void
    {
        // J/((mol*K)) - nested parentheses not allowed
        $this->expectException(FormatException::class);
        DerivedUnit::parse('J/((mol*K))');
    }

    public function testParseUnbalancedParenthesesIsInvalid(): void
    {
        // J/(mol*K - missing closing parenthesis
        $this->expectException(FormatException::class);
        DerivedUnit::parse('J/(mol*K');
    }

    public function testParseEmptyParenthesesIsInvalid(): void
    {
        // m/() - empty parentheses not allowed
        $this->expectException(FormatException::class);
        DerivedUnit::parse('m/()');
    }

    public function testParseFormatRoundTrip(): void
    {
        // Parsing formatted output should produce equivalent unit
        $original = DerivedUnit::parse('J/(mol*K)');
        $formatted = $original->format(true);
        $reparsed = DerivedUnit::parse($formatted);

        $this->assertTrue($original->equal($reparsed));
        $this->assertSame($original->dimension, $reparsed->dimension);
    }

    public function testParseFormatRoundTripUnicode(): void
    {
        // Round-trip with Unicode format
        $original = DerivedUnit::parse('W/(m2*K4)');
        $formatted = $original->format(); // Unicode format
        $reparsed = DerivedUnit::parse($formatted);

        $this->assertTrue($original->equal($reparsed));
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
        $this->assertSame('m/s²', (string)$du);
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
        $this->assertSame('m/s2', $du->format(true));
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
        $this->assertSame('m/s', $du->format());
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
        $this->assertSame('1', $du->dimension);
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
        $unitTerm = UnitTerm::parse('kg');

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
        $this->assertSame('m/s', $result->format(true));
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
        $this->assertSame('s/m', $inv->format(true));
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
        $this->assertSame('kg*m/s2', $du->format(true));
    }

    /**
     * Test sorting puts complex dimensions (more dimension terms) before simpler ones.
     */
    public function testSortingComplexDimensionBeforeSimple(): void
    {
        // N has dimension MLT-2 (3 terms), s has dimension T (1 term).
        // The more complex dimension should sort first.
        $s = new UnitTerm('s');
        $n = new UnitTerm('N');

        $du = new DerivedUnit([$s, $n]);

        $this->assertSame('N*s', $du->format(true));
    }

    /**
     * Test sorting by exponent when dimension letters are the same.
     *
     * Pa (ML⁻¹T⁻²) and J (ML²T⁻²) have the same dimension letters {M, L, T}
     * but different L exponents. Higher exponents sort first.
     */
    public function testSortingSameLettersDifferentExponents(): void
    {
        // J has dimension ML2T-2, Pa has dimension ML-1T-2.
        // Same letters, same count. The L exponent differs: J has 2, Pa has -1.
        // Higher exponent sorts first, so J should come before Pa.
        $pa = new UnitTerm('Pa');
        $j = new UnitTerm('J');

        $du = new DerivedUnit([$pa, $j]);

        $this->assertSame('J*Pa', $du->format(true));
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
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiPrefixedSiUnit(): void
    {
        // kilometre should convert to metre (SI base)
        $du = DerivedUnit::parse('km');
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiNonSiUnit(): void
    {
        // foot should convert to metre
        $du = DerivedUnit::parse('ft');
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiUnitWithExponent(): void
    {
        // m² stays as m²
        $du = DerivedUnit::parse('m2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiNonSiUnitWithExponent(): void
    {
        // ft² should convert to m²
        $du = DerivedUnit::parse('ft2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiPrefixedUnitWithExponent(): void
    {
        // km² should convert to m²
        $du = DerivedUnit::parse('km2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiCompoundUnit(): void
    {
        // m/s is already SI
        $du = DerivedUnit::parse('m/s');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiNonSiCompoundUnit(): void
    {
        // ft/s should convert to m*s⁻¹
        $du = DerivedUnit::parse('ft/s');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiForceUnit(): void
    {
        // kg*m/s² (force) should stay as kg*m*s⁻²
        $du = DerivedUnit::parse('kg*m/s2');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNonSiForceUnit(): void
    {
        // lb*ft/s² should convert to kg*m*s⁻²
        $du = DerivedUnit::parse('lb*ft/s2');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNamedUnitNewton(): void
    {
        // Newton (N) has dimension MLT-2, should convert to kg*m*s⁻²
        $du = DerivedUnit::parse('N');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNamedUnitJoule(): void
    {
        // Joule (J) has dimension ML²T⁻², should convert to kg*m²*s⁻²
        $du = DerivedUnit::parse('J');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s2', $si->format(true));
        $this->assertSame('ML2T-2', $si->dimension);
    }

    public function testToSiNamedUnitWatt(): void
    {
        // Watt (W) has dimension ML²T⁻³, should convert to kg*m²*s⁻³
        $du = DerivedUnit::parse('W');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s3', $si->format(true));
        $this->assertSame('ML2T-3', $si->dimension);
    }

    public function testToSiNamedUnitPascal(): void
    {
        // Pascal (Pa) has dimension ML⁻¹T⁻², should convert to kg*m⁻¹*s⁻²
        $du = DerivedUnit::parse('Pa');
        $si = $du->toSiBase();

        $this->assertSame('kg/(m*s2)', $si->format(true));
        $this->assertSame('ML-1T-2', $si->dimension);
    }

    public function testToSiNamedUnitHertz(): void
    {
        // Hertz (Hz) has dimension T⁻¹, should convert to s⁻¹
        $du = DerivedUnit::parse('Hz');
        $si = $du->toSiBase();

        $this->assertSame('s-1', $si->format(true));
        $this->assertSame('T-1', $si->dimension);
    }

    public function testToSiPrefixedNamedUnit(): void
    {
        // kN (kilonewton) has dimension MLT-2, should convert to kg*m*s⁻²
        $du = DerivedUnit::parse('kN');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiMassUnit(): void
    {
        // gram should convert to kg (SI base unit for mass has 'k' prefix)
        $du = DerivedUnit::parse('g');
        $si = $du->toSiBase();

        $this->assertSame('kg', $si->format(true));
        $this->assertSame('M', $si->dimension);
    }

    public function testToSiNonSiMassUnit(): void
    {
        // pound should convert to kg
        $du = DerivedUnit::parse('lb');
        $si = $du->toSiBase();

        $this->assertSame('kg', $si->format(true));
        $this->assertSame('M', $si->dimension);
    }

    public function testToSiEmptyUnit(): void
    {
        // Empty (dimensionless) unit should stay empty
        $du = new DerivedUnit();
        $si = $du->toSiBase();

        $this->assertSame('', $si->format(true));
        $this->assertSame('1', $si->dimension);
    }

    public function testToSiPreservesDimension(): void
    {
        // The dimension should be the same before and after toSi()
        $du = DerivedUnit::parse('ft*lb/s2');
        $si = $du->toSiBase();

        $this->assertSame($du->dimension, $si->dimension);
    }

    public function testToSiDoesNotModifyOriginal(): void
    {
        $du = DerivedUnit::parse('ft');
        $si = $du->toSiBase();

        $this->assertSame('ft', $du->format(true));
        $this->assertSame('m', $si->format(true));
    }

    public function testToSiComplexMixedUnit(): void
    {
        // mph (miles per hour) components: mi/h - both non-SI
        // Dimension is LT⁻¹, should become m/s
        $du = DerivedUnit::parse('mi/h');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiEnergyPerTime(): void
    {
        // J/s = W, dimension ML²T⁻³
        $du = DerivedUnit::parse('J/s');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s3', $si->format(true));
        $this->assertSame('ML2T-3', $si->dimension);
    }

    public function testToSiAcceleration(): void
    {
        // ft/s² should become m/s²
        $du = DerivedUnit::parse('ft/s2');
        $si = $du->toSiBase();

        $this->assertSame('m/s2', $si->format(true));
        $this->assertSame('LT-2', $si->dimension);
    }

    // endregion

    // region isSi() tests

    /**
     * Test isSi returns true for simple SI unit.
     */
    public function testIsSiReturnsTrueForSimpleSiUnit(): void
    {
        $du = DerivedUnit::parse('m');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns true for compound SI unit.
     */
    public function testIsSiReturnsTrueForCompoundSiUnit(): void
    {
        $du = DerivedUnit::parse('kg*m/s2');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns true for prefixed SI unit.
     */
    public function testIsSiReturnsTrueForPrefixedSiUnit(): void
    {
        $du = DerivedUnit::parse('km/s');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns false for Imperial unit.
     */
    public function testIsSiReturnsFalseForImperialUnit(): void
    {
        $du = DerivedUnit::parse('ft');

        $this->assertFalse($du->isSi());
    }

    /**
     * Test isSi returns false for mixed SI and Imperial units.
     */
    public function testIsSiReturnsFalseForMixedUnits(): void
    {
        $du = DerivedUnit::parse('kg*ft/s2');

        $this->assertFalse($du->isSi());
    }

    /**
     * Test isSi returns false for dimensionless unit.
     */
    public function testIsSiReturnsFalseForDimensionless(): void
    {
        $du = new DerivedUnit();

        $this->assertFalse($du->isSi());
    }

    // endregion

    // region isBase() tests

    /**
     * Test isBase returns true for simple base unit.
     */
    public function testIsBaseReturnsTrueForSimpleBaseUnit(): void
    {
        $du = DerivedUnit::parse('m');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns true for base unit with exponent.
     */
    public function testIsBaseReturnsTrueForBaseUnitWithExponent(): void
    {
        $du = DerivedUnit::parse('m2');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns true for compound of base units.
     */
    public function testIsBaseReturnsTrueForCompoundBaseUnits(): void
    {
        // kg*m/s2 - all base units (kg, m, s are non-expandable).
        $du = DerivedUnit::parse('kg*m/s2');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns false for derived unit.
     */
    public function testIsBaseReturnsFalseForDerivedUnit(): void
    {
        // Newton can be expanded to kg*m/s2.
        $du = DerivedUnit::parse('N');

        $this->assertFalse($du->isBase());
    }

    /**
     * Test isBase returns false for dimensionless unit.
     */
    public function testIsBaseReturnsFalseForDimensionless(): void
    {
        $du = new DerivedUnit();

        $this->assertFalse($du->isBase());
    }

    // endregion

    // region regex() tests

    /**
     * Test regex matches a simple unit symbol.
     */
    public function testRegexMatchesSimpleUnit(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm'));
    }

    /**
     * Test regex matches a unit with prefix and exponent.
     */
    public function testRegexMatchesUnitWithPrefixAndExponent(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'km2'));
    }

    /**
     * Test regex matches a compound unit with asterisk separator.
     */
    public function testRegexMatchesCompoundUnitWithAsterisk(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg*m'));
    }

    /**
     * Test regex matches a compound unit with division.
     */
    public function testRegexMatchesCompoundUnitWithDivision(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm/s'));
    }

    /**
     * Test regex matches a complex compound unit.
     */
    public function testRegexMatchesComplexCompoundUnit(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg*m/s2'));
    }

    /**
     * Test regex matches a unit with negative exponent.
     */
    public function testRegexMatchesUnitWithNegativeExponent(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 's-1'));
    }

    /**
     * Test regex matches a unit with superscript exponent.
     */
    public function testRegexMatchesUnitWithSuperscriptExponent(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm²'));
    }

    /**
     * Test regex matches parenthesised denominator form.
     */
    public function testRegexMatchesParenthesisedDenominatorForm(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'J/(mol*K)'));
    }

    /**
     * Test regex matches a unit with middle dot separator.
     */
    public function testRegexMatchesMiddleDotSeparator(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg·m'));
    }

    /**
     * Test regex does not match an empty string.
     */
    public function testRegexDoesNotMatchEmptyString(): void
    {
        $rx = DerivedUnit::regex();

        $this->assertSame(0, preg_match("/^$rx$/iu", ''));
    }

    // endregion

    // region isSiBase() tests

    /**
     * Test isSiBase returns true for a simple SI base unit.
     */
    public function testIsSiBaseReturnsTrueForSimpleSiBaseUnit(): void
    {
        $du = DerivedUnit::parse('m');

        $this->assertTrue($du->isSiBase());
    }

    /**
     * Test isSiBase returns true for an SI base unit with exponent.
     */
    public function testIsSiBaseReturnsTrueForSiBaseUnitWithExponent(): void
    {
        $du = DerivedUnit::parse('m2');

        $this->assertTrue($du->isSiBase());
    }

    /**
     * Test isSiBase returns true for compound SI base units.
     */
    public function testIsSiBaseReturnsTrueForCompoundSiBaseUnits(): void
    {
        // kg*m/s2 is already expressed in SI base units.
        $du = DerivedUnit::parse('kg*m/s2');

        $this->assertTrue($du->isSiBase());
    }

    /**
     * Test isSiBase returns false for prefixed SI unit (non-kg).
     */
    public function testIsSiBaseReturnsFalseForPrefixedNonKgUnit(): void
    {
        // km is not SI base; m is.
        $du = DerivedUnit::parse('km');

        $this->assertFalse($du->isSiBase());
    }

    /**
     * Test isSiBase returns false for non-SI unit.
     */
    public function testIsSiBaseReturnsFalseForNonSiUnit(): void
    {
        $du = DerivedUnit::parse('ft');

        $this->assertFalse($du->isSiBase());
    }

    /**
     * Test isSiBase returns false for named derived unit.
     */
    public function testIsSiBaseReturnsFalseForNamedDerivedUnit(): void
    {
        // Newton is not SI base; it expands to kg*m/s2.
        $du = DerivedUnit::parse('N');

        $this->assertFalse($du->isSiBase());
    }

    /**
     * Test isSiBase returns false for dimensionless unit.
     */
    public function testIsSiBaseReturnsFalseForDimensionlessUnit(): void
    {
        $du = new DerivedUnit();

        // Dimensionless toSiBase() returns empty, which equals empty.
        $this->assertFalse($du->isSiBase());
    }

    // endregion

    // region isExpandable() tests

    /**
     * Test isExpandable returns true for a named derived SI unit.
     */
    public function testIsExpandableReturnsTrueForNamedDerivedUnit(): void
    {
        // Newton can be expanded to kg*m/s2.
        $du = DerivedUnit::parse('N');

        $this->assertTrue($du->isExpandable());
    }

    /**
     * Test isExpandable returns true for a prefixed named derived unit.
     */
    public function testIsExpandableReturnsTrueForPrefixedNamedDerivedUnit(): void
    {
        // kN can be expanded.
        $du = DerivedUnit::parse('kN');

        $this->assertTrue($du->isExpandable());
    }

    /**
     * Test isExpandable returns true when at least one unit term is expandable.
     */
    public function testIsExpandableReturnsTrueWhenOneTermIsExpandable(): void
    {
        // N*m - Newton is expandable, metre is not.
        $du = DerivedUnit::parse('N*m');

        $this->assertTrue($du->isExpandable());
    }

    /**
     * Test isExpandable returns false for a simple base unit.
     */
    public function testIsExpandableReturnsFalseForBaseUnit(): void
    {
        $du = DerivedUnit::parse('m');

        $this->assertFalse($du->isExpandable());
    }

    /**
     * Test isExpandable returns false for compound base units.
     */
    public function testIsExpandableReturnsFalseForCompoundBaseUnits(): void
    {
        // kg*m/s2 is already fully expanded.
        $du = DerivedUnit::parse('kg*m/s2');

        $this->assertFalse($du->isExpandable());
    }

    /**
     * Test isExpandable returns false for dimensionless unit.
     */
    public function testIsExpandableReturnsFalseForDimensionless(): void
    {
        $du = new DerivedUnit();

        $this->assertFalse($du->isExpandable());
    }

    // endregion

    // region hasPrefixes() tests

    /**
     * Test hasPrefixes returns true when a unit term has a prefix.
     */
    public function testHasPrefixesReturnsTrueForPrefixedUnit(): void
    {
        $du = DerivedUnit::parse('km');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns true when at least one term has a prefix.
     */
    public function testHasPrefixesReturnsTrueWhenOneTermHasPrefix(): void
    {
        // km/s - km has a prefix, s does not.
        $du = DerivedUnit::parse('km/s');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns true for kg (the SI base mass unit uses a prefix).
     */
    public function testHasPrefixesReturnsTrueForKg(): void
    {
        $du = DerivedUnit::parse('kg');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false when no unit terms have prefixes.
     */
    public function testHasPrefixesReturnsFalseForUnprefixedUnit(): void
    {
        $du = DerivedUnit::parse('m');

        $this->assertFalse($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false for compound unit without prefixes.
     */
    public function testHasPrefixesReturnsFalseForCompoundUnprefixedUnit(): void
    {
        $du = DerivedUnit::parse('m/s');

        $this->assertFalse($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false for empty (dimensionless) unit.
     */
    public function testHasPrefixesReturnsFalseForDimensionless(): void
    {
        $du = new DerivedUnit();

        $this->assertFalse($du->hasPrefixes());
    }

    // endregion

    // region hasMergeableUnits() tests

    /**
     * Test hasMergeableUnits returns true when two terms share the same dimension.
     */
    public function testHasMergeableUnitsReturnsTrueForSameDimensionTerms(): void
    {
        // m and ft are both length (dimension 'L').
        $m = new UnitTerm('m');
        $ft = new UnitTerm('ft');
        $du = new DerivedUnit([$m, $ft]);

        $this->assertTrue($du->hasMergeableUnits());
    }

    /**
     * Test hasMergeableUnits returns false when all terms have different dimensions.
     */
    public function testHasMergeableUnitsReturnsFalseForDifferentDimensions(): void
    {
        // kg*m/s2 - mass, length, and time are all different dimensions.
        $du = DerivedUnit::parse('kg*m/s2');

        $this->assertFalse($du->hasMergeableUnits());
    }

    /**
     * Test hasMergeableUnits returns false for a single unit term.
     */
    public function testHasMergeableUnitsReturnsFalseForSingleTerm(): void
    {
        $du = DerivedUnit::parse('m');

        $this->assertFalse($du->hasMergeableUnits());
    }

    /**
     * Test hasMergeableUnits returns false for empty (dimensionless) unit.
     */
    public function testHasMergeableUnitsReturnsFalseForDimensionless(): void
    {
        $du = new DerivedUnit();

        $this->assertFalse($du->hasMergeableUnits());
    }

    // endregion

    // region __clone() tests

    /**
     * Test clone produces a separate instance with the same value.
     */
    public function testCloneProducesSeparateInstanceWithSameValue(): void
    {
        $original = DerivedUnit::parse('kg*m/s2');
        $cloned = clone $original;

        $this->assertTrue($original->equal($cloned));
        $this->assertNotSame($original, $cloned);
    }

    /**
     * Test clone deep clones unit terms.
     */
    public function testCloneDeepClonesUnitTerms(): void
    {
        $original = DerivedUnit::parse('m/s');
        $cloned = clone $original;

        // Unit terms should be different instances.
        foreach ($original->unitTerms as $symbol => $unitTerm) {
            $this->assertNotSame($unitTerm, $cloned->unitTerms[$symbol]);
        }
    }

    /**
     * Test modifying a clone does not affect the original.
     */
    public function testModifyingCloneDoesNotAffectOriginal(): void
    {
        $original = DerivedUnit::parse('m/s');
        $cloned = clone $original;

        // Add a unit term to the clone.
        $cloned->addUnitTerm(new UnitTerm('g', 'k'));

        // Original should be unchanged.
        $this->assertCount(2, $original->unitTerms);
        $this->assertSame('m/s', $original->format(true));
        $this->assertCount(3, $cloned->unitTerms);
    }

    /**
     * Test clone of empty derived unit.
     */
    public function testCloneOfEmptyDerivedUnit(): void
    {
        $original = new DerivedUnit();
        $cloned = clone $original;

        $this->assertTrue($original->equal($cloned));
        $this->assertNotSame($original, $cloned);
        $this->assertEmpty($cloned->unitTerms);
    }

    // endregion

    // region removePrefixes() tests

    /**
     * Test removePrefixes removes a simple prefix.
     */
    public function testRemovePrefixesRemovesSimplePrefix(): void
    {
        $du = DerivedUnit::parse('km');
        $result = $du->removePrefixes();

        $this->assertSame('m', $result->format(true));
    }

    /**
     * Test removePrefixes removes prefixes from all terms.
     */
    public function testRemovePrefixesRemovesFromAllTerms(): void
    {
        // km/ms - both terms have prefixes.
        $du = DerivedUnit::parse('km/ms');
        $result = $du->removePrefixes();

        $this->assertSame('m/s', $result->format(true));
    }

    /**
     * Test removePrefixes does not modify original.
     */
    public function testRemovePrefixesDoesNotModifyOriginal(): void
    {
        $du = DerivedUnit::parse('km');
        $result = $du->removePrefixes();

        $this->assertSame('km', $du->format(true));
        $this->assertSame('m', $result->format(true));
    }

    /**
     * Test removePrefixes on unit without prefixes.
     */
    public function testRemovePrefixesOnUnprefixedUnit(): void
    {
        $du = DerivedUnit::parse('m/s');
        $result = $du->removePrefixes();

        $this->assertSame('m/s', $result->format(true));
    }

    /**
     * Test removePrefixes preserves exponents.
     */
    public function testRemovePrefixesPreservesExponents(): void
    {
        $du = DerivedUnit::parse('km2');
        $result = $du->removePrefixes();

        $this->assertSame('m2', $result->format(true));
    }

    /**
     * Test removePrefixes on empty derived unit.
     */
    public function testRemovePrefixesOnEmptyDerivedUnit(): void
    {
        $du = new DerivedUnit();
        $result = $du->removePrefixes();

        $this->assertSame('', $result->format(true));
    }

    // endregion

    // region pow() tests

    /**
     * Test pow squares a simple unit.
     */
    public function testPowSquaresSimpleUnit(): void
    {
        $du = DerivedUnit::parse('m');
        $result = $du->pow(2);

        $this->assertSame('m2', $result->format(true));
        $this->assertSame('L2', $result->dimension);
    }

    /**
     * Test pow cubes a simple unit.
     */
    public function testPowCubesSimpleUnit(): void
    {
        $du = DerivedUnit::parse('m');
        $result = $du->pow(3);

        $this->assertSame('m3', $result->format(true));
        $this->assertSame('L3', $result->dimension);
    }

    /**
     * Test pow applies to all terms in a compound unit.
     */
    public function testPowAppliesToAllTermsInCompoundUnit(): void
    {
        // (m/s)^2 = m2/s2
        $du = DerivedUnit::parse('m/s');
        $result = $du->pow(2);

        $this->assertSame('m2/s2', $result->format(true));
        $this->assertSame('L2T-2', $result->dimension);
    }

    /**
     * Test pow with negative exponent inverts the unit.
     */
    public function testPowWithNegativeExponentInvertsUnit(): void
    {
        // m^-1 = m-1
        $du = DerivedUnit::parse('m');
        $result = $du->pow(-1);

        $this->assertSame('m-1', $result->format(true));
        $this->assertSame('L-1', $result->dimension);
    }

    /**
     * Test pow does not modify the original.
     */
    public function testPowDoesNotModifyOriginal(): void
    {
        $du = DerivedUnit::parse('m');
        $result = $du->pow(2);

        $this->assertSame('m', $du->format(true));
        $this->assertSame('m2', $result->format(true));
    }

    /**
     * Test pow preserves prefixes.
     */
    public function testPowPreservesPrefixes(): void
    {
        $du = DerivedUnit::parse('km');
        $result = $du->pow(2);

        $this->assertSame('km2', $result->format(true));
    }

    /**
     * Test pow with exponent 1 returns equivalent unit.
     */
    public function testPowWithExponentOneReturnsEquivalentUnit(): void
    {
        $du = DerivedUnit::parse('m/s');
        $result = $du->pow(1);

        $this->assertTrue($du->equal($result));
    }

    /**
     * Test pow on unit that already has exponents.
     */
    public function testPowOnUnitWithExistingExponents(): void
    {
        // (m2/s)^2 = m4/s2
        $du = DerivedUnit::parse('m2/s');
        $result = $du->pow(2);

        $this->assertSame('m4/s2', $result->format(true));
    }

    // endregion
}
