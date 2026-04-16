<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Internal;

use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Internal\CompoundUnit;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Internal\UnitTerm;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\UnitService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CompoundUnit class.
 */
#[CoversClass(CompoundUnit::class)]
class CompoundUnitTest extends TestCase
{
    // region Constructor tests

    public function testConstructorWithNull(): void
    {
        $du = new CompoundUnit(null);
        $this->assertEmpty($du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testConstructorWithNoArgument(): void
    {
        $du = new CompoundUnit();
        $this->assertEmpty($du->unitTerms);
    }

    public function testConstructorWithUnit(): void
    {
        $meter = UnitService::getBySymbol('m');
        $du = new CompoundUnit($meter);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m', $du->format(true));
    }

    public function testConstructorWithUnitTerm(): void
    {
        $unitTerm = new UnitTerm('m', 'k', 2);
        $du = new CompoundUnit($unitTerm);

        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    public function testConstructorWithArrayOfUnitTerms(): void
    {
        // kg is g with prefix k
        $kg = new UnitTerm('g', 'k');
        $m = new UnitTerm('m');
        $s = new UnitTerm('s', null, -2);

        $du = new CompoundUnit([$kg, $m, $s]);

        $this->assertCount(3, $du->unitTerms);
        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m/s2', $du->format(true));
    }

    // endregion

    // region parse() tests

    public function testParseEmptyStringReturnsDimensionless(): void
    {
        $du = CompoundUnit::parse('');
        $this->assertEmpty($du->unitTerms);
        $this->assertSame('', $du->dimension);
        $this->assertSame('', $du->format(true));
    }

    public function testParseSimpleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m', $du->format(true));
    }

    public function testParseUnitWithPrefix(): void
    {
        $du = CompoundUnit::parse('km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km', $du->format(true));
    }

    public function testParseUnitWithExponent(): void
    {
        $du = CompoundUnit::parse('m2');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testParseCompoundUnitWithAsterisk(): void
    {
        $du = CompoundUnit::parse('kg*m');
        $this->assertCount(2, $du->unitTerms);
        // Sorted by dimension order.
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithMiddot(): void
    {
        $du = CompoundUnit::parse('kg·m');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithDot(): void
    {
        $du = CompoundUnit::parse('kg.m');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('kg*m', $du->format(true));
    }

    public function testParseCompoundUnitWithDivision(): void
    {
        $du = CompoundUnit::parse('m/s');
        $this->assertCount(2, $du->unitTerms);
        $this->assertSame('m/s', $du->format(true));
    }

    public function testParseComplexUnit(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');
        $this->assertCount(3, $du->unitTerms);
        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m/s2', $du->format(true));
    }

    public function testParseNewton(): void
    {
        // Newton is kg⋅m⋅s⁻², dimension MLT-2.
        $du = CompoundUnit::parse('kg*m/s2');
        $this->assertSame('MLT-2', $du->dimension);
    }

    public function testParseWithSuperscriptExponent(): void
    {
        $du = CompoundUnit::parse('m²');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('L2', $du->dimension);
    }

    public function testParseInvalidUnit(): void
    {
        $this->expectException(UnknownUnitException::class);
        CompoundUnit::parse('xyz');
    }

    public function testParseMultipleDivisions(): void
    {
        // m/s/s should be m⋅s⁻²
        $du = CompoundUnit::parse('m/s/s');
        $this->assertSame('m/s2', $du->format(true));
    }

    public function testParseParenthesesMultipleTermsInDenominator(): void
    {
        // J/(mol*K) - energy per amount per temperature
        $du = CompoundUnit::parse('J/(mol*K)');
        $this->assertSame('J/(mol*K)', $du->format(true));
        $this->assertSame('ML2T-2N-1H-1', $du->dimension);
    }

    public function testParseParenthesesSingleTermInDenominator(): void
    {
        // m/(s) - single term in parentheses should work
        $du = CompoundUnit::parse('m/(s)');
        $this->assertSame('m/s', $du->format(true));
    }

    public function testParseParenthesesMultipleTermsInBoth(): void
    {
        // kg*m/(s2*A) - multiple terms in numerator and denominator
        $du = CompoundUnit::parse('kg*m/(s2*A)');
        $this->assertSame('kg*m/(s2*A)', $du->format(true));
    }

    public function testParseParenthesesWithMiddleDot(): void
    {
        // J/(mol·K) - using middle dot separator
        $du = CompoundUnit::parse('J/(mol·K)');
        $this->assertSame('J/(mol*K)', $du->format(true));
    }

    public function testParseParenthesesInNumeratorIsInvalid(): void
    {
        // (kg*m)/s - parentheses in numerator not allowed
        $this->expectException(FormatException::class);
        CompoundUnit::parse('(kg*m)/s');
    }

    public function testParseNestedParenthesesIsInvalid(): void
    {
        // J/((mol*K)) - nested parentheses not allowed
        $this->expectException(FormatException::class);
        CompoundUnit::parse('J/((mol*K))');
    }

    public function testParseUnbalancedParenthesesIsInvalid(): void
    {
        // J/(mol*K - missing closing parenthesis
        $this->expectException(FormatException::class);
        CompoundUnit::parse('J/(mol*K');
    }

    public function testParseEmptyParenthesesIsInvalid(): void
    {
        // m/() - empty parentheses not allowed
        $this->expectException(FormatException::class);
        CompoundUnit::parse('m/()');
    }

    public function testParseFormatRoundTrip(): void
    {
        // Parsing formatted output should produce equivalent unit
        $original = CompoundUnit::parse('J/(mol*K)');
        $formatted = $original->format(true);
        $reparsed = CompoundUnit::parse($formatted);

        $this->assertTrue($original->equal($reparsed));
        $this->assertSame($original->dimension, $reparsed->dimension);
    }

    public function testParseFormatRoundTripUnicode(): void
    {
        // Round-trip with Unicode format
        $original = CompoundUnit::parse('W/(m2*K4)');
        $formatted = $original->format(); // Unicode format
        $reparsed = CompoundUnit::parse($formatted);

        $this->assertTrue($original->equal($reparsed));
    }

    // endregion

    // region __toString() and format() tests

    public function testToStringEmpty(): void
    {
        $du = new CompoundUnit();
        $this->assertSame('', (string)$du);
    }

    public function testToStringSingleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $this->assertSame('m', (string)$du);
    }

    public function testToStringCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m*s-2');
        // __toString returns Unicode format with superscript exponents
        $this->assertSame('m/s²', (string)$du);
    }

    public function testToStringUsesUnicodeSymbol(): void
    {
        // Ohm has Unicode symbol Ω
        $du = CompoundUnit::parse('ohm');
        $this->assertSame('Ω', (string)$du);
    }

    public function testFormatAsciiEmpty(): void
    {
        $du = new CompoundUnit();
        $this->assertSame('', $du->format(true));
    }

    public function testFormatAsciiSingleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $this->assertSame('m', $du->format(true));
    }

    public function testFormatAsciiCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m*s-2');
        // format(true) returns ASCII format with '*' separator
        $this->assertSame('m/s2', $du->format(true));
    }

    public function testFormatAsciiUsesAsciiSymbol(): void
    {
        // Ohm has ASCII symbol 'ohm' and Unicode symbol 'Ω'
        $du = CompoundUnit::parse('ohm');
        $this->assertSame('ohm', $du->format(true));
    }

    public function testFormatUnicodeDefault(): void
    {
        $du = CompoundUnit::parse('m2');
        // format() defaults to Unicode
        $this->assertSame('m²', $du->format());
    }

    public function testFormatUnicodeCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m*s-1');
        $this->assertSame('m/s', $du->format());
    }

    // endregion

    // region $dimension property tests

    public function testDimensionSimpleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $this->assertSame('L', $du->dimension);
    }

    public function testDimensionUnitWithExponent(): void
    {
        $du = CompoundUnit::parse('m3');
        $this->assertSame('L3', $du->dimension);
    }

    public function testDimensionCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m/s');
        $this->assertSame('LT-1', $du->dimension);
    }

    public function testDimensionEmptyUnit(): void
    {
        $du = new CompoundUnit();
        $this->assertSame('', $du->dimension);
    }

    public function testDimensionVelocity(): void
    {
        // Velocity: length/time
        $du = CompoundUnit::parse('km/s');
        $this->assertSame('LT-1', $du->dimension);
    }

    public function testDimensionAcceleration(): void
    {
        // Acceleration: length/time²
        $du = CompoundUnit::parse('m/s2');
        $this->assertSame('LT-2', $du->dimension);
    }

    public function testDimensionForce(): void
    {
        // Force (Newton): mass⋅length/time²
        $du = CompoundUnit::parse('kg*m/s2');
        $this->assertSame('MLT-2', $du->dimension);
    }

    public function testDimensionEnergy(): void
    {
        // Energy (Joule): mass⋅length²/time²
        $du = CompoundUnit::parse('kg*m2/s2');
        $this->assertSame('ML2T-2', $du->dimension);
    }

    // endregion

    // region equal() tests

    public function testEqualSameUnits(): void
    {
        $du1 = CompoundUnit::parse('m/s');
        $du2 = CompoundUnit::parse('m/s');

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualCompoundUnits(): void
    {
        $du1 = CompoundUnit::parse('kg*m/s2');
        $du2 = CompoundUnit::parse('kg*m*s-2');

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualDifferentUnits(): void
    {
        $du1 = CompoundUnit::parse('m');
        $du2 = CompoundUnit::parse('km');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualDifferentExponents(): void
    {
        $du1 = CompoundUnit::parse('m2');
        $du2 = CompoundUnit::parse('m3');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualDifferentNumberOfTerms(): void
    {
        $du1 = CompoundUnit::parse('m');
        $du2 = CompoundUnit::parse('m*s');

        $this->assertFalse($du1->equal($du2));
    }

    public function testEqualEmptyUnits(): void
    {
        $du1 = new CompoundUnit();
        $du2 = new CompoundUnit();

        $this->assertTrue($du1->equal($du2));
    }

    public function testEqualWithNonCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertFalse($du->equal('m'));
        $this->assertFalse($du->equal(null));
        $this->assertFalse($du->equal(123));
    }

    public function testEqualDifferentPrefixes(): void
    {
        $du1 = CompoundUnit::parse('km');
        $du2 = CompoundUnit::parse('mm');

        $this->assertFalse($du1->equal($du2));
    }

    // endregion

    // region Constructor combination tests

    /**
     * The constructor builds the unit term list from the input array. Same-unit terms are combined (exponents
     * summed), zero-exponent terms are dropped, and differently-prefixed terms of the same base unit are kept
     * separate. These tests exercise those combination rules through the public constructor API.
     */
    public function testConstructorWithSingleTerm(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m')]);

        $this->assertCount(1, $cu->unitTerms);
        $this->assertSame('m', $cu->format(true));
    }

    public function testConstructorCombinesSameUnitExponents(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m', null, 2), new UnitTerm('m', null, 3)]);

        $this->assertCount(1, $cu->unitTerms);
        $this->assertSame('m5', $cu->format(true));
    }

    public function testConstructorCombinesWithDifferentExponents(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m', null, 3), new UnitTerm('m', null, -1)]);

        $this->assertCount(1, $cu->unitTerms);
        $this->assertSame('m2', $cu->format(true));
    }

    public function testConstructorDropsTermsWhenExponentsCancel(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m', null, 2), new UnitTerm('m', null, -2)]);

        $this->assertCount(0, $cu->unitTerms);
        $this->assertSame('', $cu->format(true));
    }

    public function testConstructorCombinesSamePrefix(): void
    {
        $cu = new CompoundUnit([new UnitTerm('m', 'k', 2), new UnitTerm('m', 'k', 1)]);

        $this->assertCount(1, $cu->unitTerms);
        $this->assertSame('km3', $cu->format(true));
    }

    public function testConstructorTreatsDifferentPrefixesSeparately(): void
    {
        // km and m should be treated as different unit terms (different unexponentiated symbols).
        $cu = new CompoundUnit([new UnitTerm('m', 'k'), new UnitTerm('m')]);

        $this->assertCount(2, $cu->unitTerms);
    }

    // endregion

    // region toCompoundUnit() tests

    public function testToCompoundUnitFromCompoundUnit(): void
    {
        $original = CompoundUnit::parse('kg*m/s2');
        $result = CompoundUnit::toCompoundUnit($original);

        $this->assertSame($original, $result);
    }

    public function testToCompoundUnitFromString(): void
    {
        $result = CompoundUnit::toCompoundUnit('m/s');

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('m/s', $result->format(true));
    }

    public function testToCompoundUnitFromUnit(): void
    {
        $meter = UnitService::getBySymbol('m');
        $result = CompoundUnit::toCompoundUnit($meter);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('m', $result->format(true));
    }

    public function testToCompoundUnitFromUnitTerm(): void
    {
        $unitTerm = new UnitTerm('m', 'k', 2);
        $result = CompoundUnit::toCompoundUnit($unitTerm);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('km2', $result->format(true));
    }

    public function testToCompoundUnitFromNull(): void
    {
        $result = CompoundUnit::toCompoundUnit(null);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('', $result->format(true));
    }

    // endregion

    // region inv() tests

    public function testInvSingleUnit(): void
    {
        $du = CompoundUnit::parse('m');
        $inv = $du->inv();

        $this->assertSame('m-1', $inv->format(true));
        $this->assertSame('L-1', $inv->dimension);
    }

    public function testInvUnitWithExponent(): void
    {
        $du = CompoundUnit::parse('m2');
        $inv = $du->inv();

        $this->assertSame('m-2', $inv->format(true));
    }

    public function testInvCompoundUnit(): void
    {
        $du = CompoundUnit::parse('m/s');
        $inv = $du->inv();

        // m⋅s⁻¹ inverted is m⁻¹⋅s
        $this->assertSame('s/m', $inv->format(true));
    }

    public function testInvDoesNotModifyOriginal(): void
    {
        $du = CompoundUnit::parse('m');
        $inv = $du->inv();

        $this->assertSame('m', $du->format(true));
        $this->assertSame('m-1', $inv->format(true));
    }

    public function testInvEmpty(): void
    {
        $du = new CompoundUnit();
        $inv = $du->inv();

        $this->assertSame('', $inv->format(true));
    }

    // endregion

    // region mul() tests

    /**
     * Test mul() combines two simple units.
     */
    public function testMulCombinesSimpleUnits(): void
    {
        $a = CompoundUnit::parse('kg');
        $b = CompoundUnit::parse('m');
        $result = $a->mul($b);

        $this->assertSame('kg*m', $result->format(true));
        $this->assertSame('ML', $result->dimension);
    }

    /**
     * Test mul() combines exponents of the same unit.
     */
    public function testMulCombinesSameUnitExponents(): void
    {
        $a = CompoundUnit::parse('m');
        $b = CompoundUnit::parse('m2');
        $result = $a->mul($b);

        $this->assertSame('m3', $result->format(true));
        $this->assertSame('L3', $result->dimension);
    }

    /**
     * Test mul() cancels units with opposite exponents.
     */
    public function testMulCancelsOppositeExponents(): void
    {
        $a = CompoundUnit::parse('m');
        $b = CompoundUnit::parse('m-1');
        $result = $a->mul($b);

        $this->assertSame('', $result->format(true));
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test mul() with compound units.
     */
    public function testMulCompoundUnits(): void
    {
        $a = CompoundUnit::parse('kg*m');
        $b = CompoundUnit::parse('s-2');
        $result = $a->mul($b);

        $this->assertSame('kg*m/s2', $result->format(true));
        $this->assertSame('MLT-2', $result->dimension);
    }

    /**
     * Test mul() with empty CompoundUnit is identity.
     */
    public function testMulWithEmptyIsIdentity(): void
    {
        $a = CompoundUnit::parse('m/s');
        $b = new CompoundUnit();
        $result = $a->mul($b);

        $this->assertSame('m/s', $result->format(true));
    }

    /**
     * Test mul() does not modify the original.
     */
    public function testMulDoesNotModifyOriginal(): void
    {
        $a = CompoundUnit::parse('m');
        $b = CompoundUnit::parse('s');
        $result = $a->mul($b);

        $this->assertSame('m', $a->format(true));
        $this->assertSame('s', $b->format(true));
        $this->assertSame('m*s', $result->format(true));
    }

    // endregion

    // region Sorting tests

    public function testSortingByDimensionOrderMassLengthTime(): void
    {
        // Add in reverse dimension order: time, length, mass.
        $s = new UnitTerm('s');
        $m = new UnitTerm('m');
        $kg = new UnitTerm('g', 'k');

        $du = new CompoundUnit([$s, $m, $kg]);

        // Sorted by dimension order: M, L, T.
        $this->assertSame('kg*m*s', $du->format(true));
    }

    public function testSortingMixedExponents(): void
    {
        // kg⋅m⋅s⁻² - force units.
        $s = new UnitTerm('s', null, -2);
        $m = new UnitTerm('m');
        $kg = new UnitTerm('g', 'k');

        $du = new CompoundUnit([$s, $m, $kg]);

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

        $du = new CompoundUnit([$s, $n]);

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

        $du = new CompoundUnit([$pa, $j]);

        $this->assertSame('J*Pa', $du->format(true));
    }

    // endregion

    // region Edge cases

    public function testCombiningSameUnitsViaMultiplication(): void
    {
        // m * m should give m²
        $du = CompoundUnit::parse('m*m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testCombiningSameUnitsViaDivision(): void
    {
        // m / m should give empty (dimensionless)
        $du = CompoundUnit::parse('m/m');
        $this->assertCount(0, $du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testComplexCombination(): void
    {
        // m³ / m should give m²
        $du = CompoundUnit::parse('m3/m');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('m2', $du->format(true));
    }

    public function testCombiningSamePrefixedUnitsViaMultiplication(): void
    {
        // km * km should give km²
        $du = CompoundUnit::parse('km*km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    public function testCombiningSamePrefixedUnitsViaDivision(): void
    {
        // km / km should give empty (dimensionless)
        $du = CompoundUnit::parse('km/km');
        $this->assertCount(0, $du->unitTerms);
        $this->assertSame('', $du->format(true));
    }

    public function testComplexPrefixedCombination(): void
    {
        // km³ / km should give km²
        $du = CompoundUnit::parse('km3/km');
        $this->assertCount(1, $du->unitTerms);
        $this->assertSame('km2', $du->format(true));
    }

    // endregion

    // region toSi() tests

    public function testToSiSimpleSiUnit(): void
    {
        // meter is already SI base unit
        $du = CompoundUnit::parse('m');
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiPrefixedSiUnit(): void
    {
        // kilometer should convert to meter (SI base)
        $du = CompoundUnit::parse('km');
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiNonSiUnit(): void
    {
        // foot should convert to meter
        $du = CompoundUnit::parse('ft');
        $si = $du->toSiBase();

        $this->assertSame('m', $si->format(true));
        $this->assertSame('L', $si->dimension);
    }

    public function testToSiUnitWithExponent(): void
    {
        // m² stays as m²
        $du = CompoundUnit::parse('m2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiNonSiUnitWithExponent(): void
    {
        // ft² should convert to m²
        $du = CompoundUnit::parse('ft2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiPrefixedUnitWithExponent(): void
    {
        // km² should convert to m²
        $du = CompoundUnit::parse('km2');
        $si = $du->toSiBase();

        $this->assertSame('m2', $si->format(true));
        $this->assertSame('L2', $si->dimension);
    }

    public function testToSiCompoundUnit(): void
    {
        // m/s is already SI
        $du = CompoundUnit::parse('m/s');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiNonSiCompoundUnit(): void
    {
        // ft/s should convert to m*s⁻¹
        $du = CompoundUnit::parse('ft/s');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiForceUnit(): void
    {
        // kg*m/s² (force) should stay as kg*m*s⁻²
        $du = CompoundUnit::parse('kg*m/s2');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNonSiForceUnit(): void
    {
        // lb*ft/s² should convert to kg*m*s⁻²
        $du = CompoundUnit::parse('lb*ft/s2');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNamedUnitNewton(): void
    {
        // Newton (N) has dimension MLT-2, should convert to kg*m*s⁻²
        $du = CompoundUnit::parse('N');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiNamedUnitJoule(): void
    {
        // Joule (J) has dimension ML²T⁻², should convert to kg*m²*s⁻²
        $du = CompoundUnit::parse('J');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s2', $si->format(true));
        $this->assertSame('ML2T-2', $si->dimension);
    }

    public function testToSiNamedUnitWatt(): void
    {
        // Watt (W) has dimension ML²T⁻³, should convert to kg*m²*s⁻³
        $du = CompoundUnit::parse('W');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s3', $si->format(true));
        $this->assertSame('ML2T-3', $si->dimension);
    }

    public function testToSiNamedUnitPascal(): void
    {
        // Pascal (Pa) has dimension ML⁻¹T⁻², should convert to kg*m⁻¹*s⁻²
        $du = CompoundUnit::parse('Pa');
        $si = $du->toSiBase();

        $this->assertSame('kg/(m*s2)', $si->format(true));
        $this->assertSame('ML-1T-2', $si->dimension);
    }

    public function testToSiNamedUnitHertz(): void
    {
        // Hertz (Hz) has dimension T⁻¹, should convert to s⁻¹
        $du = CompoundUnit::parse('Hz');
        $si = $du->toSiBase();

        $this->assertSame('s-1', $si->format(true));
        $this->assertSame('T-1', $si->dimension);
    }

    public function testToSiPrefixedNamedUnit(): void
    {
        // kN (kilonewton) has dimension MLT-2, should convert to kg*m*s⁻²
        $du = CompoundUnit::parse('kN');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s2', $si->format(true));
        $this->assertSame('MLT-2', $si->dimension);
    }

    public function testToSiMassUnit(): void
    {
        // gram should convert to kg (SI base unit for mass has 'k' prefix)
        $du = CompoundUnit::parse('g');
        $si = $du->toSiBase();

        $this->assertSame('kg', $si->format(true));
        $this->assertSame('M', $si->dimension);
    }

    public function testToSiNonSiMassUnit(): void
    {
        // pound should convert to kg
        $du = CompoundUnit::parse('lb');
        $si = $du->toSiBase();

        $this->assertSame('kg', $si->format(true));
        $this->assertSame('M', $si->dimension);
    }

    public function testToSiEmptyUnit(): void
    {
        // Empty (dimensionless) unit should stay empty
        $du = new CompoundUnit();
        $si = $du->toSiBase();

        $this->assertSame('', $si->format(true));
        $this->assertSame('', $si->dimension);
    }

    public function testToSiPreservesDimension(): void
    {
        // The dimension should be the same before and after toSi()
        $du = CompoundUnit::parse('ft*lb/s2');
        $si = $du->toSiBase();

        $this->assertSame($du->dimension, $si->dimension);
    }

    public function testToSiDoesNotModifyOriginal(): void
    {
        $du = CompoundUnit::parse('ft');
        $si = $du->toSiBase();

        $this->assertSame('ft', $du->format(true));
        $this->assertSame('m', $si->format(true));
    }

    public function testToSiComplexMixedUnit(): void
    {
        // mph (miles per hour) components: mi/h - both non-SI
        // Dimension is LT⁻¹, should become m/s
        $du = CompoundUnit::parse('mi/h');
        $si = $du->toSiBase();

        $this->assertSame('m/s', $si->format(true));
        $this->assertSame('LT-1', $si->dimension);
    }

    public function testToSiEnergyPerTime(): void
    {
        // J/s = W, dimension ML²T⁻³
        $du = CompoundUnit::parse('J/s');
        $si = $du->toSiBase();

        $this->assertSame('kg*m2/s3', $si->format(true));
        $this->assertSame('ML2T-3', $si->dimension);
    }

    public function testToSiAcceleration(): void
    {
        // ft/s² should become m/s²
        $du = CompoundUnit::parse('ft/s2');
        $si = $du->toSiBase();

        $this->assertSame('m/s2', $si->format(true));
        $this->assertSame('LT-2', $si->dimension);
    }

    public function testToSiMixedExpandableAndBase(): void
    {
        // N*s mixes an expandable term (N → kg*m/s2) with a base term (s).
        // Result: kg*m/s2 * s = kg*m/s. Verifies the internal expansion correctly handles
        // compounds where some terms expand and others don't.
        $du = CompoundUnit::parse('N*s');
        $si = $du->toSiBase();

        $this->assertSame('kg*m/s', $si->format(true));
        $this->assertSame('MLT-1', $si->dimension);
    }

    // endregion

    // region isSi() tests

    /**
     * Test isSi returns true for simple SI unit.
     */
    public function testIsSiReturnsTrueForSimpleSiUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns true for compound SI unit.
     */
    public function testIsSiReturnsTrueForCompoundSiUnit(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns true for prefixed SI unit.
     */
    public function testIsSiReturnsTrueForPrefixedSiUnit(): void
    {
        $du = CompoundUnit::parse('km/s');

        $this->assertTrue($du->isSi());
    }

    /**
     * Test isSi returns false for Imperial unit.
     */
    public function testIsSiReturnsFalseForImperialUnit(): void
    {
        $du = CompoundUnit::parse('ft');

        $this->assertFalse($du->isSi());
    }

    /**
     * Test isSi returns false for mixed SI and Imperial units.
     */
    public function testIsSiReturnsFalseForMixedUnits(): void
    {
        $du = CompoundUnit::parse('kg*ft/s2');

        $this->assertFalse($du->isSi());
    }

    /**
     * Test isSi returns false for dimensionless unit.
     */
    public function testIsSiReturnsFalseForDimensionless(): void
    {
        $du = new CompoundUnit();

        $this->assertFalse($du->isSi());
    }

    // endregion

    // region isBase() tests

    /**
     * Test isBase returns true for simple base unit.
     */
    public function testIsBaseReturnsTrueForSimpleBaseUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns true for base unit with exponent.
     */
    public function testIsBaseReturnsTrueForBaseUnitWithExponent(): void
    {
        $du = CompoundUnit::parse('m2');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns true for compound of base units.
     */
    public function testIsBaseReturnsTrueForCompoundBaseUnits(): void
    {
        // kg*m/s2 - all base units (kg, m, s are non-expandable).
        $du = CompoundUnit::parse('kg*m/s2');

        $this->assertTrue($du->isBase());
    }

    /**
     * Test isBase returns false for compound unit.
     */
    public function testIsBaseReturnsFalseForCompoundUnit(): void
    {
        // Newton can be expanded to kg*m/s2.
        $du = CompoundUnit::parse('N');

        $this->assertFalse($du->isBase());
    }

    /**
     * Test isBase returns true for dimensionless unit.
     */
    public function testIsBaseReturnsTrueForDimensionless(): void
    {
        $du = new CompoundUnit();

        $this->assertTrue($du->isBase());
    }

    // endregion

    // region regex() tests

    /**
     * Test regex matches a simple unit symbol.
     */
    public function testRegexMatchesSimpleUnit(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm'));
    }

    /**
     * Test regex matches a unit with prefix and exponent.
     */
    public function testRegexMatchesUnitWithPrefixAndExponent(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'km2'));
    }

    /**
     * Test regex matches a compound unit with asterisk separator.
     */
    public function testRegexMatchesCompoundUnitWithAsterisk(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg*m'));
    }

    /**
     * Test regex matches a compound unit with division.
     */
    public function testRegexMatchesCompoundUnitWithDivision(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm/s'));
    }

    /**
     * Test regex matches a complex compound unit.
     */
    public function testRegexMatchesComplexCompoundUnit(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg*m/s2'));
    }

    /**
     * Test regex matches a unit with negative exponent.
     */
    public function testRegexMatchesUnitWithNegativeExponent(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 's-1'));
    }

    /**
     * Test regex matches a unit with superscript exponent.
     */
    public function testRegexMatchesUnitWithSuperscriptExponent(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'm²'));
    }

    /**
     * Test regex matches parenthesised denominator form.
     */
    public function testRegexMatchesParenthesisedDenominatorForm(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'J/(mol*K)'));
    }

    /**
     * Test regex matches a unit with middle dot separator.
     */
    public function testRegexMatchesMiddleDotSeparator(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(1, preg_match("/^$rx$/iu", 'kg·m'));
    }

    /**
     * Test regex does not match an empty string.
     */
    public function testRegexDoesNotMatchEmptyString(): void
    {
        $rx = CompoundUnit::regex();

        $this->assertSame(0, preg_match("/^$rx$/iu", ''));
    }

    // endregion


    // region hasPrefixes() tests

    /**
     * Test hasPrefixes returns true when a unit term has a prefix.
     */
    public function testHasPrefixesReturnsTrueForPrefixedUnit(): void
    {
        $du = CompoundUnit::parse('km');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns true when at least one term has a prefix.
     */
    public function testHasPrefixesReturnsTrueWhenOneTermHasPrefix(): void
    {
        // km/s - km has a prefix, s does not.
        $du = CompoundUnit::parse('km/s');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns true for kg (the SI base mass unit uses a prefix).
     */
    public function testHasPrefixesReturnsTrueForKg(): void
    {
        $du = CompoundUnit::parse('kg');

        $this->assertTrue($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false when no unit terms have prefixes.
     */
    public function testHasPrefixesReturnsFalseForUnprefixedUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertFalse($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false for compound unit without prefixes.
     */
    public function testHasPrefixesReturnsFalseForCompoundUnprefixedUnit(): void
    {
        $du = CompoundUnit::parse('m/s');

        $this->assertFalse($du->hasPrefixes());
    }

    /**
     * Test hasPrefixes returns false for empty (dimensionless) unit.
     */
    public function testHasPrefixesReturnsFalseForDimensionless(): void
    {
        $du = new CompoundUnit();

        $this->assertFalse($du->hasPrefixes());
    }

    // endregion

    // region isMergeable() tests

    /**
     * Test isMergeable returns true when two terms share the same dimension.
     */
    public function testIsMergeableReturnsTrueForSameDimensionTerms(): void
    {
        // m and ft are both length (dimension 'L').
        $m = new UnitTerm('m');
        $ft = new UnitTerm('ft');
        $du = new CompoundUnit([$m, $ft]);

        $this->assertTrue($du->isMergeable());
    }

    /**
     * Test isMergeable returns false when all terms have different dimensions.
     */
    public function testIsMergeableReturnsFalseForDifferentDimensions(): void
    {
        // kg*m/s2 - mass, length, and time are all different dimensions.
        $du = CompoundUnit::parse('kg*m/s2');

        $this->assertFalse($du->isMergeable());
    }

    /**
     * Test isMergeable returns false for a single unit term.
     */
    public function testIsMergeableReturnsFalseForSingleTerm(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertFalse($du->isMergeable());
    }

    /**
     * Test isMergeable returns false for empty (dimensionless) unit.
     */
    public function testIsMergeableReturnsFalseForDimensionless(): void
    {
        $du = new CompoundUnit();

        $this->assertFalse($du->isMergeable());
    }

    // endregion

    // region __clone() tests

    /**
     * Test clone produces a separate instance with the same value.
     */
    public function testCloneProducesSeparateInstanceWithSameValue(): void
    {
        $original = CompoundUnit::parse('kg*m/s2');
        $cloned = clone $original;

        $this->assertTrue($original->equal($cloned));
        $this->assertNotSame($original, $cloned);
    }

    /**
     * Test clone deep clones unit terms.
     */
    public function testCloneDeepClonesUnitTerms(): void
    {
        $original = CompoundUnit::parse('m/s');
        $cloned = clone $original;

        // Unit terms should be different instances.
        foreach ($original->unitTerms as $symbol => $unitTerm) {
            $this->assertNotSame($unitTerm, $cloned->unitTerms[$symbol]);
        }
    }

    /**
     * Test that operations returning a new CompoundUnit don't mutate the original.
     *
     * CompoundUnit is externally immutable — mutators are private and arithmetic methods like mul() return new
     * instances. This test exercises that guarantee via the public API.
     */
    public function testMulDoesNotMutateOriginal(): void
    {
        $original = CompoundUnit::parse('m/s');
        $other = CompoundUnit::parse('kg');

        $result = $original->mul($other);

        // Original should be unchanged.
        $this->assertCount(2, $original->unitTerms);
        $this->assertSame('m/s', $original->format(true));
        // Result has one more term.
        $this->assertCount(3, $result->unitTerms);
    }

    /**
     * Test that addUnitTerm cannot be called externally. This pins the private visibility of the mutator, which is
     * load-bearing for the deep-immutability guarantee.
     */
    public function testAddUnitTermIsPrivate(): void
    {
        $cu = CompoundUnit::parse('m');

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('private method');

        // @phpstan-ignore method.notFound
        $cu->addUnitTerm(new UnitTerm('s'));
    }

    /**
     * Test clone of empty compound unit.
     */
    public function testCloneOfEmptyCompoundUnit(): void
    {
        $original = new CompoundUnit();
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
        $du = CompoundUnit::parse('km');
        $result = $du->removePrefixes();

        $this->assertSame('m', $result->format(true));
    }

    /**
     * Test removePrefixes removes prefixes from all terms.
     */
    public function testRemovePrefixesRemovesFromAllTerms(): void
    {
        // km/ms - both terms have prefixes.
        $du = CompoundUnit::parse('km/ms');
        $result = $du->removePrefixes();

        $this->assertSame('m/s', $result->format(true));
    }

    /**
     * Test removePrefixes does not modify original.
     */
    public function testRemovePrefixesDoesNotModifyOriginal(): void
    {
        $du = CompoundUnit::parse('km');
        $result = $du->removePrefixes();

        $this->assertSame('km', $du->format(true));
        $this->assertSame('m', $result->format(true));
    }

    /**
     * Test removePrefixes on unit without prefixes.
     */
    public function testRemovePrefixesOnUnprefixedUnit(): void
    {
        $du = CompoundUnit::parse('m/s');
        $result = $du->removePrefixes();

        $this->assertSame('m/s', $result->format(true));
    }

    /**
     * Test removePrefixes preserves exponents.
     */
    public function testRemovePrefixesPreservesExponents(): void
    {
        $du = CompoundUnit::parse('km2');
        $result = $du->removePrefixes();

        $this->assertSame('m2', $result->format(true));
    }

    /**
     * Test removePrefixes on empty compound unit.
     */
    public function testRemovePrefixesOnEmptyCompoundUnit(): void
    {
        $du = new CompoundUnit();
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
        $du = CompoundUnit::parse('m');
        $result = $du->pow(2);

        $this->assertSame('m2', $result->format(true));
        $this->assertSame('L2', $result->dimension);
    }

    /**
     * Test pow cubes a simple unit.
     */
    public function testPowCubesSimpleUnit(): void
    {
        $du = CompoundUnit::parse('m');
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
        $du = CompoundUnit::parse('m/s');
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
        $du = CompoundUnit::parse('m');
        $result = $du->pow(-1);

        $this->assertSame('m-1', $result->format(true));
        $this->assertSame('L-1', $result->dimension);
    }

    /**
     * Test pow does not modify the original.
     */
    public function testPowDoesNotModifyOriginal(): void
    {
        $du = CompoundUnit::parse('m');
        $result = $du->pow(2);

        $this->assertSame('m', $du->format(true));
        $this->assertSame('m2', $result->format(true));
    }

    /**
     * Test pow preserves prefixes.
     */
    public function testPowPreservesPrefixes(): void
    {
        $du = CompoundUnit::parse('km');
        $result = $du->pow(2);

        $this->assertSame('km2', $result->format(true));
    }

    /**
     * Test pow with exponent 1 returns equivalent unit.
     */
    public function testPowWithExponentOneReturnsEquivalentUnit(): void
    {
        $du = CompoundUnit::parse('m/s');
        $result = $du->pow(1);

        $this->assertTrue($du->equal($result));
    }

    /**
     * Test pow on unit that already has exponents.
     */
    public function testPowOnUnitWithExistingExponents(): void
    {
        // (m2/s)^2 = m4/s2
        $du = CompoundUnit::parse('m2/s');
        $result = $du->pow(2);

        $this->assertSame('m4/s2', $result->format(true));
    }

    // endregion

    // region siPreferred() tests

    /**
     * Test siPreferred returns true for pure SI unit.
     */
    public function testSiPreferredForPureSiUnit(): void
    {
        $du = CompoundUnit::parse('kg');

        $this->assertTrue($du->siPreferred());
    }

    /**
     * Test siPreferred returns false for pure English unit.
     */
    public function testSiPreferredReturnsFalseForEnglishUnit(): void
    {
        $du = CompoundUnit::parse('lb');

        $this->assertFalse($du->siPreferred());
    }

    /**
     * Test siPreferred returns true for mixed SI and English units.
     */
    public function testSiPreferredReturnsTrueForMixedUnits(): void
    {
        // kg (SI) and ft (English) — has both, but at least one unambiguous SI unit.
        $du = CompoundUnit::parse('kg*ft');

        $this->assertTrue($du->siPreferred());
    }

    /**
     * Test siPreferred returns true for common base unit that is ambiguous.
     */
    public function testSiPreferredForAmbiguousBaseUnit(): void
    {
        // 's' is used with both SI and English systems, so it's in the common list.
        // With no English units and no unambiguous SI units, nEnglishUnits === 0 → true.
        $du = CompoundUnit::parse('s');

        $this->assertTrue($du->siPreferred());
    }

    /**
     * Test siPreferred returns false for English compound unit.
     */
    public function testSiPreferredReturnsFalseForEnglishCompound(): void
    {
        // lb*ft/s2 — lb and ft are English, s is ambiguous.
        $du = CompoundUnit::parse('lb*ft/s2');

        $this->assertFalse($du->siPreferred());
    }

    // endregion

    // region includesUnit() tests

    /**
     * Test includesUnit returns true for a unit in the compound unit.
     */
    public function testIncludesUnitReturnsTrueForIncludedUnit(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');
        $meter = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $meter);

        $this->assertTrue($du->includesUnit($meter));
    }

    /**
     * Test includesUnit returns false for a unit not in the compound unit.
     */
    public function testIncludesUnitReturnsFalseForNonIncludedUnit(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');
        $foot = UnitService::getBySymbol('ft');
        $this->assertInstanceOf(Unit::class, $foot);

        $this->assertFalse($du->includesUnit($foot));
    }

    /**
     * Test includesUnit with prefixed unit term finds the base unit.
     */
    public function testIncludesUnitWithPrefixedTerm(): void
    {
        $du = CompoundUnit::parse('km');
        $meter = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $meter);

        $this->assertTrue($du->includesUnit($meter));
    }

    /**
     * Test includesUnit on a single-term compound unit.
     */
    public function testIncludesUnitOnSingleTerm(): void
    {
        $du = CompoundUnit::parse('ft');
        $foot = UnitService::getBySymbol('ft');
        $this->assertInstanceOf(Unit::class, $foot);

        $this->assertTrue($du->includesUnit($foot));
    }

    // endregion

    // region toEnglishBase() tests

    /**
     * Test toEnglishBase for length dimension.
     */
    public function testToEnglishBaseForLength(): void
    {
        $du = CompoundUnit::parse('m');
        $result = $du->toEnglishBase();

        $this->assertSame('ft', $result->asciiSymbol);
    }

    /**
     * Test toEnglishBase for mass dimension.
     */
    public function testToEnglishBaseForMass(): void
    {
        $du = CompoundUnit::parse('kg');
        $result = $du->toEnglishBase();

        $this->assertSame('lb', $result->asciiSymbol);
    }

    /**
     * Test toEnglishBase for compound dimension.
     */
    public function testToEnglishBaseForCompoundDimension(): void
    {
        // Force: MLT-2. English base should be lb*ft/s2.
        $du = CompoundUnit::parse('N');
        $result = $du->toEnglishBase();

        $this->assertSame('lb*ft/s2', $result->asciiSymbol);
    }

    /**
     * Test toEnglishBase for time dimension falls back to SI base.
     */
    public function testToEnglishBaseForTimeFallsBackToSi(): void
    {
        // Time has no English base unit, falls back to SI (seconds).
        $du = CompoundUnit::parse('h');
        $result = $du->toEnglishBase();

        $this->assertSame('s', $result->asciiSymbol);
    }

    // endregion

    // region tryExpand() tests

    // Most of tryExpand()'s observable behaviour is covered by the toSiBase() tests above
    // (e.g. N → kg*m/s2, kN → kg*m/s2, N*s → kg*m/s) and by the multiplier property tests
    // for the prefix magnitudes. The tests below cover the *internal* signals that are not
    // directly observable through the public API: the null-return contract for base and
    // unexpandable inputs, and the caching behaviour.

    /**
     * Test tryExpand returns null for a base unit (internal signal — not observable via toSi).
     */
    public function testTryExpandReturnsNullForBaseUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertNull($du->tryExpand());
    }

    /**
     * Test tryExpand returns null when a compound unit contains only base terms (internal signal).
     */
    public function testTryExpandReturnsNullForUnexpandableTerm(): void
    {
        // A base unit compound like kg*m is already base — nothing to expand.
        $du = CompoundUnit::parse('kg*m');

        $this->assertNull($du->tryExpand());
    }

    /**
     * Test tryExpand returns the cached expansion on a second call. Internal optimisation,
     * verified by identity (===) on the returned Quantity.
     */
    public function testTryExpandReturnsCachedExpansion(): void
    {
        $du = CompoundUnit::parse('N');

        $expansion1 = $du->tryExpand();
        $expansion2 = $du->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion1);
        $this->assertSame($expansion1, $expansion2);
    }

    // endregion

    // region multiplier property tests

    /**
     * Test multiplier property for a single unprefixed unit.
     */
    public function testMultiplierPropertyForSingleUnit(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertSame(1.0, $du->multiplier);
    }

    /**
     * Test multiplier property for a prefixed unit.
     */
    public function testMultiplierPropertyForPrefixedUnit(): void
    {
        $du = CompoundUnit::parse('km');

        $this->assertSame(1000.0, $du->multiplier);
    }

    /**
     * Test multiplier property for a prefixed unit with exponent.
     */
    public function testMultiplierPropertyForPrefixedUnitWithExponent(): void
    {
        // km2 => (1000)^2 = 1e6
        $du = CompoundUnit::parse('km2');

        $this->assertSame(1e6, $du->multiplier);
    }

    /**
     * Test multiplier property for compound unit with multiple prefixes.
     */
    public function testMultiplierPropertyForCompoundUnit(): void
    {
        // kg*km => 1000 * 1000 = 1e6 (kg prefix = 1000, km prefix = 1000)
        $du = CompoundUnit::parse('kg*km');

        $this->assertSame(1e6, $du->multiplier);
    }

    /**
     * Test multiplier property for empty CompoundUnit.
     */
    public function testMultiplierPropertyForEmptyUnit(): void
    {
        $du = new CompoundUnit();

        $this->assertSame(1.0, $du->multiplier);
    }

    // endregion

    // region firstUnitTerm property tests

    /**
     * Test firstUnitTerm property returns first unit term.
     */
    public function testFirstUnitTermPropertyReturnsSingleTerm(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertInstanceOf(UnitTerm::class, $du->firstUnitTerm);
        $this->assertSame('meter', $du->firstUnitTerm->unit->name);
    }

    /**
     * Test firstUnitTerm property returns the first term (in input order) in a compound unit
     * with multiple terms.
     */
    public function testFirstUnitTermPropertyReturnsFirstInCompound(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');

        $this->assertInstanceOf(UnitTerm::class, $du->firstUnitTerm);
        $this->assertSame('kg', $du->firstUnitTerm->asciiSymbol);
    }

    /**
     * Test firstUnitTerm property returns null for empty CompoundUnit.
     */
    public function testFirstUnitTermPropertyReturnsNullForEmpty(): void
    {
        $du = new CompoundUnit();

        $this->assertNull($du->firstUnitTerm);
    }

    // endregion

    // region quantityType property tests

    /**
     * Test quantityType property returns QuantityType for a registered dimension.
     */
    public function testQuantityTypePropertyReturnsQuantityType(): void
    {
        $du = CompoundUnit::parse('m');

        $this->assertNotNull($du->quantityType);
        $this->assertSame('length', $du->quantityType->name);
    }

    /**
     * Test quantityType property returns null for an unregistered dimension.
     */
    public function testQuantityTypePropertyReturnsNullForUnregisteredDimension(): void
    {
        // L5T3 doesn't correspond to any registered quantity type.
        $du = CompoundUnit::parse('m5*s3');

        $this->assertNull($du->quantityType);
    }

    // endregion

    // region merge() tests

    /**
     * Test merge combines compatible unit terms.
     */
    public function testMergeCombinesCompatibleUnits(): void
    {
        // m*ft — both are length, should merge to a single length unit.
        $du = CompoundUnit::parse('m*ft');
        $result = $du->merge();

        $this->assertInstanceOf(Quantity::class, $result);
        // m*ft: ft → m conversion is 0.3048, so 1 m*ft = 0.3048 m2.
        $this->assertSame('m2', $result->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(0.3048, $result->value, 1e-4);
    }

    /**
     * Test merge returns value of 1.0 when no conversion is needed.
     */
    public function testMergeWithNoConversionNeeded(): void
    {
        // m*m — same unit, should merge to m2 with value 1.0.
        $du = CompoundUnit::parse('m*m');

        // Note: m*m should already combine in the parser to m2.
        // Let's use a unit that doesn't auto-combine.
        $result = $du->merge();

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertSame(1.0, $result->value);
    }

    /**
     * Test merge on a unit with no mergeable terms returns unchanged.
     */
    public function testMergeWithNoMergeableTerms(): void
    {
        $du = CompoundUnit::parse('kg*m/s2');
        $result = $du->merge();

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertSame(1.0, $result->value);
        $this->assertSame('kg*m/s2', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test merge with different units of the same dimension in a compound.
     */
    public function testMergeWithDifferentUnitsOfSameDimension(): void
    {
        // kg*lb — both are mass, should merge.
        $du = CompoundUnit::parse('kg*lb');
        $result = $du->merge();

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertSame('kg2', $result->compoundUnit->asciiSymbol);
        // 1 lb ≈ 0.45359 kg, so 1 kg*lb ≈ 0.45359 kg2.
        $this->assertEqualsWithDelta(0.45359, $result->value, 1e-4);
    }

    // endregion
}
