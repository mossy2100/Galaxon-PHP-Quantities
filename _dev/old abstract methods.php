<?php

use Galaxon\Core\Floats;

// region Abstract methods

/**
 * Define the base and derived units for this measurement type.
 *
 * Returns an associative array where keys are unit symbols and values are integers specifying the prefix sets
 * allowed for that unit.
 *
 * These units should NOT include multiplier (e.g. metric or binary) prefixes (e.g., use 'g' not 'kg', 'm' not
 * 'km').
 *
 * Prefix set values:
 * - 0: No prefixes allowed
 * - PREFIX_CODE_METRIC: All metric prefixes (quecto to quetta)
 * - PREFIX_CODE_SMALL_METRIC: Small metric prefixes only (quecto to deci)
 * - PREFIX_CODE_LARGE_METRIC: Large metric prefixes only (deca to quetta)
 * - PREFIX_CODE_BINARY: Binary prefixes (Ki, Mi, Gi, etc.)
 * - PREFIX_CODE_LARGE: All binary and large metric prefixes (k, Ki, etc.)
 *
 * @return array<string, int> Map of unit symbol to prefix set flags.
 *
 * @example
 *   return [
 *       'm'   => self::PREFIX_CODE_METRIC,        // metre (all metric prefixes)
 *       'ft'  => 0,                            // foot (no prefixes)
 *       'rad' => self::PREFIX_CODE_SMALL_METRIC,  // radian (only small metric prefixes)
 *       'B'   => self::PREFIX_CODE_LARGE,         // byte (binary and large metric)
 *   ];
 */
public static function getUnits(): array
{
    return [];
}

/**
 * Define conversion factors between different units.
 *
 * Each conversion is an array with 3 or 4 elements:
 * - [0] string: Source unit symbol
 * - [1] string: Destination unit symbol
 * - [2] float: Multiplier (must be non-zero)
 * - [3] float: Optional offset (for affine conversions like temperature)
 *
 * Only direct conversions need to be specified; the system will automatically
 * find paths for indirect conversions (e.g., if you have m→ft and ft→in, it
 * can automatically convert m→in).
 *
 * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
 *
 * @example
 *   return [
 *       ['m', 'ft', 3.28084],          // 1 m = 3.28084 ft
 *       ['ft', 'in', 12],              // 1 ft = 12 in
 *       ['C', 'F', 1.8, 32],           // F = C * 1.8 + 32
 *   ];
 */
public static function getConversions(): array
{
    return [];
}

// endregion


// region Extraction methods

/**
 * Get the units for Angle measurements.
 *
 * @return array<string, int> Array of units with allowed prefixes flags.
 */
#[Override]
    public static function getUnits(): array
{
    return [
        'rad'    => self::PREFIX_CODE_SMALL_METRIC,  // radian
        'deg'    => 0,  // degree
        'arcmin' => 0,  // arcminute
        'arcsec' => 0,  // arcsecond
        'as'     => self::PREFIX_CODE_SMALL_METRIC,  // arcsecond (alias used with prefixes)
        'grad'   => 0,  // gradian
        'turn'   => 0,  // turn/revolution
    ];
}

    /**
     * Get the conversions for Angle measurements.
     *
     * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
     */
    #[Override]
    public static function getConversions(): array
{
    return [
        ['turn', 'rad', Floats::TAU],
        ['turn', 'deg', 360],
        ['deg', 'arcmin', 60],
        ['arcmin', 'arcsec', 60],
        ['arcsec', 'as', 1],
        ['turn', 'grad', 400],
    ];
}

// endregion

 // region Extraction methods

    /**
     * Get the units for Area measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getUnits(): array
{
    return [
        'm2'  => self::PREFIX_CODE_METRIC,  // square metre
        'ha'  => 0,  // hectare
        'ac'  => 0,  // acre
        'mi2' => 0,  // square mile
        'yd2' => 0,  // square yard
        'ft2' => 0,  // square foot
        'in2' => 0,  // square inch
    ];
}

    /**
     * Get the conversions for Area measurements.
     *
     * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
     */
    #[Override]
    public static function getConversions(): array
{
    return [
        // Metric.
        ['ha', 'm2', 10000],
        // Metric-imperial bridge.
        ['ac', 'm2', 4046.8564224],
        // Imperial.
        ['mi2', 'ac', 640],
        ['ac', 'yd2', 4840],
        ['yd2', 'ft2', 9],
        ['ft2', 'in2', 144],
    ];
}

// endregion

// region Extraction methods

    /**
     * Get the units for Memory measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getUnits(): array
{
    return [
        'B' => self::PREFIX_CODE_LARGE,  // byte
        'b' => self::PREFIX_CODE_LARGE,  // bit
    ];
}

    /**
     * Get the conversions for Memory measurements.
     *
     * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
     */
    #[Override]
    public static function getConversions(): array
{
    return [
        // 1 byte = 8 bits
        ['B', 'b', 8]
    ];
}

// endregion


    // region Extraction methods

    /**
     * Get the units for Length measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getUnits(): array
{
    return [
        'm'  => self::PREFIX_CODE_METRIC,  // metre
        'px' => 0,  // pixel
        'pt' => 0,  // point
        'in' => 0,  // inch
        'ft' => 0,  // foot
        'yd' => 0,  // yard
        'mi' => 0,  // mile
        'au' => 0,  // astronomical unit
        'ly' => 0,  // light-year
        'pc' => 0,  // parsec
    ];
}

    /**
     * Get the conversions for Length measurements.
     *
     * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
     */
    public static function getConversions(): array
{
    return [
        // Metric-imperial bridge.
        ['in', 'mm', 25.4],
        // Imperial.
        ['in', 'px', 96],
        ['in', 'pt', 72],
        ['ft', 'in', 12],
        ['yd', 'ft', 3],
        ['mi', 'yd', 1760],
        // Astronomical.
        ['au', 'm', 149597870700],
        ['ly', 'm', 9460730472580800],
        ['pc', 'au', 648000 / M_PI],
    ];
}

// endregion


    // region Extraction methods

    /**
     * Get the units for Mass measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getUnits(): array
{
    return [
        'g'   => self::PREFIX_CODE_METRIC,        // gram
        't'   => self::PREFIX_CODE_LARGE_METRIC,  // tonne
        'gr'  => 0,  // grain
        'oz'  => 0,  // ounce
        'lb'  => 0,  // pound
        'st'  => 0,  // stone
        'ton' => 0,  // ton
    ];
}

    /**
     * Get the conversions for Mass measurements.
     *
     * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
     */
    #[Override]
    public static function getConversions(): array
{
    return [
        // Metric.
        ['t', 'kg', 1000],
        // Metric-imperial bridge.
        ['lb', 'g', 453.59237],
        // Imperial.
        ['lb', 'oz', 16],
        ['st', 'lb', 14],
        // Use US short ton by default.
        ['ton', 'lb', 2000],
    ];
}

// endregion


    // region Extraction methods

    /**
     * Get the units for Temperature measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getUnits(): array
{
    return [
        'K' => self::PREFIX_CODE_METRIC,  // Kelvin
        'C' => 0,  // Celsius
        'F' => 0,  // Fahrenheit
    ];
}

    /**
     * Get the conversions for Temperature measurements.
     *
     * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
     */
    #[Override]
    public static function getConversions(): array
{
    return [
        ['C', 'K', 1, 273.15],
        ['C', 'F', 1.8, 32],
    ];
}

// endregion


    // region Extraction methods

    /**
     * Get the units for Time measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getUnits(): array
{
    return [
        's'   => self::PREFIX_CODE_METRIC,  // second
        'min' => 0,  // minute
        'h'   => 0,  // hour
        'd'   => 0,  // day
        'w'   => 0,  // week
        'mo'  => 0,  // month
        'y'   => 0,  // year
        'c'   => 0,  // century
    ];
}

    /**
     * Get the conversions for Time measurements.
     *
     * These conversion factors are basic. Leap seconds are not considered, and the year-to-day conversion is based on
     * the average length of a year in the Gregorian calendar. If you want, you can add or update conversions using the
     * `Time::getUnitConverter()->addConversion()` method.
     *
     * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
     */
    #[Override]
    public static function getConversions(): array
{
    return [
        ['min', 's', 60],
        ['h', 'min', 60],
        ['d', 'h', 24],
        ['w', 'd', 7],
        ['y', 'mo', 12],
        ['y', 'd', 365.2425],
        ['c', 'y', 100]
    ];
}

// endregion

 // region Extraction methods

    /**
     * Get the units for Volume measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getUnits(): array
{
    return [
        'm3'    => self::PREFIX_CODE_METRIC,  // cubic metre
        'L'     => self::PREFIX_CODE_METRIC,  // litre
        'in3'   => 0,  // cubic inch
        'ft3'   => 0,  // cubic foot
        'yd3'   => 0,  // cubic yard
        'gal'   => 0,  // gallon
        'qt'    => 0,  // quart
        'pt'    => 0,  // pint
        'c'     => 0,  // cup
        'floz'  => 0,  // fluid ounce
        'tbsp'  => 0,  // tablespoon
        'tsp'   => 0,  // teaspoon
    ];
}

    /**
     * Get the conversions for Volume measurements.
     *
     * @return array<array{0: string, 1: string, 2: float, 3?: float}> Array of conversion definitions.
     */
    #[Override]
    public static function getConversions(): array
{
    return [
        // Metric.
        ['m3', 'L', 1000],
        // Metric-imperial bridge.
        ['in3', 'mL', 16.387064],
        // Imperial.
        ['ft3', 'in3', 1728],
        ['yd3', 'ft3', 27],
        ['gal', 'qt', 4],
        ['gal', 'in3', 231],
        ['qt', 'pt', 2],
        ['pt', 'c', 2],
        ['c', 'floz', 8],
        ['floz', 'tbsp', 2],
        ['tbsp', 'tsp', 3]
    ];
}

// endregion
