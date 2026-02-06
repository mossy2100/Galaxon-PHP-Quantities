<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;

/**
 * Provides access to physical constants as Quantity objects.
 *
 * Constants are lazily instantiated and cached for efficient repeated access.
 * Use the static methods for IDE autocompletion, or get() for symbol-based lookup.
 */
class PhysicalConstant
{
    // region Constants

    /**
     * Map of symbols to their static method names.
     *
     * @var array<string, string>
     */
    private const array SYMBOL_MAP = [
        // SI defining constants.
        'deltaNuCs' => 'caesiumFrequency',
        'c'         => 'speedOfLight',
        'h'         => 'planck',
        'e'         => 'elementaryCharge',
        'k'         => 'boltzmann',
        'NA'        => 'avogadro',
        'Kcd'       => 'luminousEfficacy',
        // Gravitational constants.
        'g'         => 'earthGravity',
        'G'         => 'gravitational',
        // Electromagnetic constants.
        'epsilon0'  => 'vacuumPermittivity',
        'mu0'       => 'vacuumPermeability',
        // Atomic and nuclear constants.
        'me'        => 'electronMass',
        'mp'        => 'protonMass',
        'mn'        => 'neutronMass',
        'alpha'     => 'fineStructure',
        'Rinf'      => 'rydberg',
        'a0'        => 'bohrRadius',
        // Thermodynamic constants.
        'R'         => 'molarGas',
        'sigma'     => 'stefanBoltzmann',
    ];

    // endregion

    // region Static properties

    /**
     * Cache of instantiated constant Quantity objects, keyed by symbol.
     *
     * @var array<string, Quantity>
     */
    private static array $cache = [];

    // endregion

    // region Private helper

    /**
     * Get or create a cached constant.
     *
     * @param string $symbol The constant's symbol (cache key).
     * @param float $value The constant's value.
     * @param string|null $unitSymbol The unit symbol (null for dimensionless).
     * @return Quantity The cached Quantity object.
     */
    private static function cached(string $symbol, float $value, ?string $unitSymbol): Quantity
    {
        return self::$cache[$symbol] ??= Quantity::create($value, $unitSymbol);
    }

    // endregion

    // region Symbol-based lookup

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

        $method = self::SYMBOL_MAP[$symbol];
        return self::$method();
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
        return self::cached('deltaNuCs', 9192631770, 'Hz');
    }

    /**
     * Speed of light in vacuum (c).
     *
     * Defines the metre: exactly 299,792,458 m/s.
     */
    public static function speedOfLight(): Quantity
    {
        return self::cached('c', 299792458, 'm/s');
    }

    /**
     * Planck constant (h).
     *
     * Defines the kilogram: exactly 6.62607015 × 10⁻³⁴ J·s.
     */
    public static function planck(): Quantity
    {
        return self::cached('h', 6.62607015e-34, 'J*s');
    }

    /**
     * Elementary charge (e).
     *
     * Defines the ampere: exactly 1.602176634 × 10⁻¹⁹ C.
     */
    public static function elementaryCharge(): Quantity
    {
        return self::cached('e', 1.602176634e-19, 'C');
    }

    /**
     * Boltzmann constant (k).
     *
     * Defines the kelvin: exactly 1.380649 × 10⁻²³ J/K.
     */
    public static function boltzmann(): Quantity
    {
        return self::cached('k', 1.380649e-23, 'J/K');
    }

    /**
     * Avogadro constant (Nᴀ).
     *
     * Defines the mole: exactly 6.02214076 × 10²³ mol⁻¹.
     */
    public static function avogadro(): Quantity
    {
        return self::cached('NA', 6.02214076e23, 'mol-1');
    }

    /**
     * Luminous efficacy of 540 THz radiation (Kcd).
     *
     * Defines the candela: exactly 683 lm/W.
     */
    public static function luminousEfficacy(): Quantity
    {
        return self::cached('Kcd', 683, 'lm/W');
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
        return self::cached('g', 9.80665, 'm/s2');
    }

    /**
     * Newtonian constant of gravitation (G).
     *
     * Value: 6.67430 × 10⁻¹¹ m³/(kg·s²).
     */
    public static function gravitational(): Quantity
    {
        return self::cached('G', 6.67430e-11, 'm3/(kg*s2)');
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
        return self::cached('epsilon0', 8.8541878128e-12, 'F/m');
    }

    /**
     * Vacuum magnetic permeability (μ₀).
     *
     * Value: 1.25663706212 × 10⁻⁶ H/m.
     */
    public static function vacuumPermeability(): Quantity
    {
        return self::cached('mu0', 1.25663706212e-6, 'H/m');
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
        return self::cached('me', 9.1093837015e-31, 'kg');
    }

    /**
     * Proton mass (mₚ).
     *
     * Value: 1.67262192369 × 10⁻²⁷ kg.
     */
    public static function protonMass(): Quantity
    {
        return self::cached('mp', 1.67262192369e-27, 'kg');
    }

    /**
     * Neutron mass (mₙ).
     *
     * Value: 1.67492749804 × 10⁻²⁷ kg.
     */
    public static function neutronMass(): Quantity
    {
        return self::cached('mn', 1.67492749804e-27, 'kg');
    }

    /**
     * Fine-structure constant (α).
     *
     * Dimensionless. Value: 7.2973525693 × 10⁻³.
     */
    public static function fineStructure(): Quantity
    {
        return self::cached('alpha', 7.2973525693e-3, null);
    }

    /**
     * Rydberg constant (R∞).
     *
     * Value: 10,973,731.568160 m⁻¹.
     */
    public static function rydberg(): Quantity
    {
        return self::cached('Rinf', 10973731.568160, 'm-1');
    }

    /**
     * Bohr radius (a₀).
     *
     * Value: 5.29177210903 × 10⁻¹¹ m.
     */
    public static function bohrRadius(): Quantity
    {
        return self::cached('a0', 5.29177210903e-11, 'm');
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
        return self::cached('R', 8.314462618, 'J/(mol*K)');
    }

    /**
     * Stefan-Boltzmann constant (σ).
     *
     * Value: 5.670374419 × 10⁻⁸ W/(m²·K⁴).
     */
    public static function stefanBoltzmann(): Quantity
    {
        return self::cached('sigma', 5.670374419e-8, 'W/(m2*K4)');
    }

    // endregion
}
