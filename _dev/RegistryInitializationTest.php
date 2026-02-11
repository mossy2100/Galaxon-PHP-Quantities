<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Registry;

use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\AmountOfSubstance;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Capacitance;
use Galaxon\Quantities\QuantityType\CatalyticActivity;
use Galaxon\Quantities\QuantityType\Conductance;
use Galaxon\Quantities\QuantityType\Data;
use Galaxon\Quantities\QuantityType\Density;
use Galaxon\Quantities\QuantityType\Dimensionless;
use Galaxon\Quantities\QuantityType\ElectricCharge;
use Galaxon\Quantities\QuantityType\ElectricCurrent;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Illuminance;
use Galaxon\Quantities\QuantityType\Inductance;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\LuminousFlux;
use Galaxon\Quantities\QuantityType\LuminousIntensity;
use Galaxon\Quantities\QuantityType\MagneticFlux;
use Galaxon\Quantities\QuantityType\MagneticFluxDensity;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Power;
use Galaxon\Quantities\QuantityType\Pressure;
use Galaxon\Quantities\QuantityType\RadiationDose;
use Galaxon\Quantities\QuantityType\Resistance;
use Galaxon\Quantities\QuantityType\SolidAngle;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\QuantityType\Voltage;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Registry\ConversionRegistry;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\QuantityTypeRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests that exercise registry initialization to ensure coverage of
 * getUnitDefinitions() and getConversionDefinitions() in all QuantityType classes.
 *
 * These methods are typically only called once during static initialization,
 * which can happen before PHPUnit starts tracking coverage. This test resets
 * the registries and triggers re-initialization during test execution.
 */
#[CoversClass(UnitRegistry::class)]
#[CoversClass(ConversionRegistry::class)]
#[CoversClass(QuantityTypeRegistry::class)]
#[CoversClass(PrefixRegistry::class)]
#[CoversClass(Acceleration::class)]
#[CoversClass(AmountOfSubstance::class)]
#[CoversClass(Angle::class)]
#[CoversClass(Area::class)]
#[CoversClass(Capacitance::class)]
#[CoversClass(CatalyticActivity::class)]
#[CoversClass(Conductance::class)]
#[CoversClass(Data::class)]
#[CoversClass(Density::class)]
#[CoversClass(Dimensionless::class)]
#[CoversClass(ElectricCharge::class)]
#[CoversClass(ElectricCurrent::class)]
#[CoversClass(Energy::class)]
#[CoversClass(Force::class)]
#[CoversClass(Frequency::class)]
#[CoversClass(Illuminance::class)]
#[CoversClass(Inductance::class)]
#[CoversClass(Length::class)]
#[CoversClass(LuminousFlux::class)]
#[CoversClass(LuminousIntensity::class)]
#[CoversClass(MagneticFlux::class)]
#[CoversClass(MagneticFluxDensity::class)]
#[CoversClass(Mass::class)]
#[CoversClass(Power::class)]
#[CoversClass(Pressure::class)]
#[CoversClass(RadiationDose::class)]
#[CoversClass(Resistance::class)]
#[CoversClass(SolidAngle::class)]
#[CoversClass(Temperature::class)]
#[CoversClass(Time::class)]
#[CoversClass(Velocity::class)]
#[CoversClass(Voltage::class)]
#[CoversClass(Volume::class)]
final class RegistryInitializationTest extends TestCase
{
    /**
     * Test that registry initialization loads all unit and conversion definitions.
     *
     * This test resets both registries and then triggers initialization by
     * loading all measurement systems. This ensures that getUnitDefinitions()
     * and getConversionDefinitions() are called on all QuantityType classes
     * during test execution, allowing proper code coverage tracking.
     */
    public function testRegistryInitializationLoadsAllDefinitions(): void
    {
        // Reset all registries to clear any cached state.
        QuantityTypeRegistry::reset();
        UnitRegistry::reset();
        ConversionRegistry::reset();

        // Load all measurement systems.
        // This triggers UnitRegistry::init() which calls:
        // - getUnitDefinitions() on all QuantityType classes
        // - ConversionRegistry::loadConversions() which calls:
        //   - getAllConversionDefinitions() which calls:
        //     - getConversionDefinitions() on all QuantityType classes
        //     - getUnitDefinitions() again for expansion-based conversions
        UnitRegistry::loadSystem(System::Si);
        UnitRegistry::loadSystem(System::SiAccepted);
        UnitRegistry::loadSystem(System::Common);
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
        UnitRegistry::loadSystem(System::Scientific);
        UnitRegistry::loadSystem(System::Astronomical);
        UnitRegistry::loadSystem(System::Nautical);
        UnitRegistry::loadSystem(System::Typographical);

        // Verify that units were loaded.
        $loadedSystems = UnitRegistry::getLoadedSystems();
        $this->assertContains(System::Si, $loadedSystems);
        $this->assertContains(System::Imperial, $loadedSystems);

        // Verify some units from different systems exist.
        $this->assertNotNull(UnitRegistry::getBySymbol('m'));   // SI
        $this->assertNotNull(UnitRegistry::getBySymbol('ft'));  // Imperial
        $this->assertNotNull(UnitRegistry::getBySymbol('B'));   // Common (data)

        // Verify conversions were loaded.
        // Note: Conversions are stored by unit symbols that exist in the registry.
        $lengthConversions = ConversionRegistry::getByDimension('L');
        $this->assertNotEmpty($lengthConversions);

        $massConversions = ConversionRegistry::getByDimension('M');
        $this->assertNotEmpty($massConversions);
    }

    /**
     * Test that QuantityTypeRegistry initialization loads all quantity types.
     *
     * This test resets the registry and triggers re-initialization,
     * ensuring that init() is called during test execution for proper
     * code coverage tracking.
     */
    public function testQuantityTypeRegistryInitializationLoadsAllTypes(): void
    {
        // Reset the registry.
        QuantityTypeRegistry::reset();

        // Trigger initialization by accessing quantity types.
        $allTypes = QuantityTypeRegistry::getAll();

        // Verify quantity types were loaded.
        $this->assertNotEmpty($allTypes);

        // Verify SI base dimensions exist.
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('L'));  // length
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('M'));  // mass
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('T'));  // time
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('I'));  // electric current
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('H'));  // temperature
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('N'));  // amount of substance
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('J'));  // luminous intensity

        // Verify derived dimensions exist.
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('L2')); // area
        $this->assertNotNull(QuantityTypeRegistry::getByDimension('L3')); // volume

        // Verify lookup by name works.
        $this->assertNotNull(QuantityTypeRegistry::getByName('length'));
        $this->assertNotNull(QuantityTypeRegistry::getByName('energy'));

        // Verify lookup by class works.
        $this->assertNotNull(QuantityTypeRegistry::getByClass(Length::class));
    }

    /**
     * Test that PrefixRegistry initialization loads all prefix definitions.
     *
     * This test resets the prefix cache and triggers re-initialization,
     * ensuring that getPrefixDefinitions() and init() are called during
     * test execution for proper code coverage tracking.
     */
    public function testPrefixUtilityInitializationLoadsAllPrefixes(): void
    {
        // Reset the prefix cache.
        PrefixRegistry::reset();

        // Trigger initialization by accessing prefixes.
        $allPrefixes = PrefixRegistry::getPrefixes();

        // Verify prefixes were loaded.
        $this->assertNotEmpty($allPrefixes);

        // Verify we have prefixes from all groups.
        $smallEng = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_SMALL_ENG_METRIC);
        $this->assertNotEmpty($smallEng);

        $largeEng = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_LARGE_ENG_METRIC);
        $this->assertNotEmpty($largeEng);

        $binary = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_BINARY);
        $this->assertNotEmpty($binary);

        // Verify specific prefixes exist.
        $this->assertNotNull(PrefixRegistry::getBySymbol('k'));   // kilo
        $this->assertNotNull(PrefixRegistry::getBySymbol('m'));   // milli
        $this->assertNotNull(PrefixRegistry::getBySymbol('μ'));   // micro (Unicode)
        $this->assertNotNull(PrefixRegistry::getBySymbol('Ki'));  // kibi
    }
}
