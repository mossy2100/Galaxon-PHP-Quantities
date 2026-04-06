<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Floats;

/**
 * Provides access to physical constants as Quantity objects.
 *
 * Constants are lazily instantiated and cached for efficient repeated access.
 * Use the static methods for IDE autocompletion, or get() for symbol-based lookup.
 */
class PhysicalConstant
{
    // region Private constants

    /**
     * Map of symbols to their definitions.
     *
     * Each entry contains:
     * - 'method' — The static accessor method name.
     * - 'value' — The numeric value (null for derived constants computed at runtime).
     * - 'unit' — The unit symbol (null for dimensionless or derived).
     *
     * @var array<string, array{method: string, value?: float, unit?: string}>
     */
    private const array SYMBOL_MAP = [
        // SI defining constants.
        'deltaNuCs' => [
            'method' => 'caesiumFrequency',
            'value'  => 9192631770.0,
            'unit'   => 'Hz',
        ],
        'c'         => [
            'method' => 'speedOfLight',
            'value'  => 299792458.0,
            'unit'   => 'm/s',
        ],
        'h'         => [
            'method' => 'planck',
            'value'  => 6.62607015e-34,
            'unit'   => 'J*s',
        ],
        'e'         => [
            'method' => 'elementaryCharge',
            'value'  => 1.602176634e-19,
            'unit'   => 'C',
        ],
        'k'         => [
            'method' => 'boltzmann',
            'value'  => 1.380649e-23,
            'unit'   => 'J/K',
        ],
        'NA'        => [
            'method' => 'avogadro',
            'value'  => 6.02214076e23,
            'unit'   => 'mol-1',
        ],
        'Kcd'       => [
            'method' => 'luminousEfficacy',
            'value'  => 683.0,
            'unit'   => 'lm/W',
        ],
        // Gravitational constants.
        'g'         => [
            'method' => 'earthGravity',
            'value'  => 9.80665,
            'unit'   => 'm/s2',
        ],
        'G'         => [
            'method' => 'gravitational',
            'value'  => 6.67430e-11,
            'unit'   => 'm3/(kg*s2)',
        ],
        // Electromagnetic constants.
        'epsilon0'  => [
            'method' => 'vacuumPermittivity',
            'value'  => 8.8541878128e-12,
            'unit'   => 'F/m',
        ],
        'mu0'       => [
            'method' => 'vacuumPermeability',
            'value'  => 1.25663706212e-6,
            'unit'   => 'H/m',
        ],
        // Atomic and nuclear constants.
        'me'        => [
            'method' => 'electronMass',
            'value'  => 9.1093837015e-31,
            'unit'   => 'kg',
        ],
        'mp'        => [
            'method' => 'protonMass',
            'value'  => 1.67262192369e-27,
            'unit'   => 'kg',
        ],
        'mn'        => [
            'method' => 'neutronMass',
            'value'  => 1.67492749804e-27,
            'unit'   => 'kg',
        ],
        'alpha'     => [
            'method' => 'fineStructure',
            'value'  => 7.2973525693e-3,
            'unit'   => null,
        ],
        'Rinf'      => [
            'method' => 'rydberg',
            'value'  => 10973731.568160,
            'unit'   => 'm-1',
        ],
        'a0'        => [
            'method' => 'bohrRadius',
            'value'  => 5.29177210903e-11,
            'unit'   => 'm',
        ],
        // Thermodynamic constants.
        'R'         => [
            'method' => 'molarGas',
            'value'  => 8.314462618,
            'unit'   => 'J/(mol*K)',
        ],
        'sigma'     => [
            'method' => 'stefanBoltzmann',
            'value'  => 5.670374419e-8,
            'unit'   => 'W/(m2*K4)',
        ],
        // Derived constants (value/unit are null — computed at runtime).
        'hbar'      => [
            'method' => 'reducedPlanck',
        ],
    ];

    // endregion

    // region Private static properties

    /**
     * Cache of instantiated constant Quantity objects, keyed by symbol.
     *
     * @var array<string, Quantity>
     */
    private static array $cache = [];

    // endregion

    // region Helper methods

    /**
     * Get or create a cached constant from the SYMBOL_MAP definition.
     *
     * @param string $symbol The constant's symbol (cache key).
     * @return Quantity The cached Quantity object.
     */
    private static function cached(string $symbol): Quantity
    {
        return self::$cache[$symbol] ??= Quantity::create(
            self::SYMBOL_MAP[$symbol]['value'],
            self::SYMBOL_MAP[$symbol]['unit']
        );
    }

    // endregion

    // region Lookup methods

    /**
     * Get a physical constant by its symbol.
     *
     * Symbol lookup is case-sensitive (e.g., 'G' differs from 'g').
     *
     * @param string $symbol The symbol of the constant (e.g., 'G', 'c', 'h').
     * @return Quantity The constant as a Quantity object.
     * @throws DomainException If the symbol is unknown.
     */
    public static function get(string $symbol): Quantity
    {
        if (!isset(self::SYMBOL_MAP[$symbol])) {
            throw new DomainException("Unknown constant symbol: '$symbol'.");
        }

        $method = self::SYMBOL_MAP[$symbol]['method'];
        return self::$method();
    }

    /**
     * Get all the physical constants as an array of Quantity objects, keyed by symbol.
     *
     * @return array<string, Quantity>
     */
    public static function getAll(): array
    {
        $constants = [];
        foreach (self::SYMBOL_MAP as $symbol => $info) {
            $method = $info['method'];
            $constants[$symbol] = self::$method();
        }
        return $constants;
    }

    // endregion

    // region SI defining constants

    /**
     * Hyperfine transition frequency of caesium (ΔνCs).
     *
     * Defines the second: exactly 9,192,631,770 Hz.
     */
    public static function caesiumFrequency(): Quantity
    {
        return self::cached('deltaNuCs');
    }

    /**
     * Speed of light in vacuum (c).
     *
     * Defines the meter: exactly 299,792,458 m/s.
     */
    public static function speedOfLight(): Quantity
    {
        return self::cached('c');
    }

    /**
     * Planck constant (h).
     *
     * Defines the kilogram: exactly 6.62607015 × 10⁻³⁴ J·s.
     */
    public static function planck(): Quantity
    {
        return self::cached('h');
    }

    /**
     * Elementary charge (e).
     *
     * Defines the ampere: exactly 1.602176634 × 10⁻¹⁹ C.
     */
    public static function elementaryCharge(): Quantity
    {
        return self::cached('e');
    }

    /**
     * Boltzmann constant (k).
     *
     * Defines the kelvin: exactly 1.380649 × 10⁻²³ J/K.
     */
    public static function boltzmann(): Quantity
    {
        return self::cached('k');
    }

    /**
     * Avogadro constant (Nᴀ).
     *
     * Defines the mole: exactly 6.02214076 × 10²³ mol⁻¹.
     */
    public static function avogadro(): Quantity
    {
        return self::cached('NA');
    }

    /**
     * Luminous efficacy of 540 THz radiation (Kcd).
     *
     * Defines the candela: exactly 683 lm/W.
     */
    public static function luminousEfficacy(): Quantity
    {
        return self::cached('Kcd');
    }

    // endregion

    // region Gravitational constants

    /**
     * Standard acceleration of gravity at the surface of Earth (g₀).
     *
     * Value: 9.80665 m/s² (exact by definition).
     */
    public static function earthGravity(): Quantity
    {
        return self::cached('g');
    }

    /**
     * Newtonian constant of gravitation (G).
     *
     * Value: 6.67430 × 10⁻¹¹ m³/(kg·s²).
     */
    public static function gravitational(): Quantity
    {
        return self::cached('G');
    }

    // endregion

    // region Electromagnetic constants

    /**
     * Vacuum electric permittivity (ε₀).
     *
     * Value: 8.8541878128 × 10⁻¹² F/m.
     */
    public static function vacuumPermittivity(): Quantity
    {
        return self::cached('epsilon0');
    }

    /**
     * Vacuum magnetic permeability (μ₀).
     *
     * Value: 1.25663706212 × 10⁻⁶ H/m.
     */
    public static function vacuumPermeability(): Quantity
    {
        return self::cached('mu0');
    }

    // endregion

    // region Atomic and nuclear constants

    /**
     * Electron mass (mₑ).
     *
     * Value: 9.1093837015 × 10⁻³¹ kg.
     */
    public static function electronMass(): Quantity
    {
        return self::cached('me');
    }

    /**
     * Proton mass (mₚ).
     *
     * Value: 1.67262192369 × 10⁻²⁷ kg.
     */
    public static function protonMass(): Quantity
    {
        return self::cached('mp');
    }

    /**
     * Neutron mass (mₙ).
     *
     * Value: 1.67492749804 × 10⁻²⁷ kg.
     */
    public static function neutronMass(): Quantity
    {
        return self::cached('mn');
    }

    /**
     * Fine-structure constant (α).
     *
     * Dimensionless. Value: 7.2973525693 × 10⁻³.
     */
    public static function fineStructure(): Quantity
    {
        return self::cached('alpha');
    }

    /**
     * Rydberg constant (R∞).
     *
     * Value: 10,973,731.568160 m⁻¹.
     */
    public static function rydberg(): Quantity
    {
        return self::cached('Rinf');
    }

    /**
     * Bohr radius (a₀).
     *
     * Value: 5.29177210903 × 10⁻¹¹ m.
     */
    public static function bohrRadius(): Quantity
    {
        return self::cached('a0');
    }

    // endregion

    // region Thermodynamic constants

    /**
     * Molar gas constant (R).
     *
     * Value: 8.314462618 J/(mol·K).
     */
    public static function molarGas(): Quantity
    {
        return self::cached('R');
    }

    /**
     * Stefan-Boltzmann constant (σ).
     *
     * Value: 5.670374419 × 10⁻⁸ W/(m²·K⁴).
     */
    public static function stefanBoltzmann(): Quantity
    {
        return self::cached('sigma');
    }

    // endregion

    // region Derived constants

    /**
     * Reduced Planck constant (ℏ = h / τ).
     *
     * Computed from Planck constant for full precision. Not stored in SYMBOL_MAP since it
     * is derived from another constant rather than defined as a literal value.
     */
    public static function reducedPlanck(): Quantity
    {
        return self::$cache['hbar'] ??= self::planck()->div(Floats::TAU);
    }

    // endregion
}
