# Quantities Package Audit

**Date:** 2026-02-06
**Reviewer:** Claude (Opus 4.5)
**Package:** `galaxon/quantities`
**Source files reviewed:** ~50 PHP files, ~14k lines

---

## 1. Architecture

### Strengths

- **Clean layered design**: The hierarchy `Unit → UnitTerm → DerivedUnit → Quantity` is logical and well-structured. Each layer adds meaningful abstraction.
- **Registry pattern**: The multiton `UnitRegistry`, `ConversionRegistry`, and `QuantityTypeRegistry` provide centralized management with lazy initialization.
- **Dimension-based type safety**: Using dimension codes (e.g., `MLT-2` for force) enables compile-time-like guarantees that operations are dimensionally consistent.
- **Graph-based conversion**: The `Converter` class uses pathfinding with error tracking to find optimal conversion paths - a sophisticated approach.
- **Separation of concerns**: Configuration (unit definitions in QuantityType subclasses) is cleanly separated from behavior (Quantity, Converter).

### Potential Concerns

- **Static state**: Heavy reliance on static registries means testing requires careful reset handling. Consider dependency injection for testability.
- **Tight coupling in Converter**: The `findNextConversion()` method is ~110 lines with nested loops. Could benefit from extraction into smaller methods or a dedicated graph traversal class.
- **Circular references**: `Converter` depends on `Quantity::create()` (in `addMergedUnit`/`addExpandedUnit`), while `Quantity` depends on `Converter`. This works but adds complexity.

---

## 2. SWE Best Practices

### Strengths

- **PHPStan level 9**: Excellent static analysis coverage.
- **Immutability**: Most objects are effectively immutable via `private(set)` and `readonly`.
- **Property hooks**: Modern PHP 8.4+ features used appropriately for computed properties.
- **Comprehensive exception handling**: Clear use of `DomainException`, `FormatException`, `LogicException` with meaningful messages.
- **Code organization**: Consistent use of `// region` markers for logical grouping.

### Areas for Improvement

- **TODOs in production code**: `UnitTerm.php:302` has a TODO about ensuring symbol uniqueness. Should be addressed or tracked.
- **Magic numbers**: Some values like `0.5` (half ULP) in `FloatWithError` could be named constants.
- **Commented debug code**: `Converter.php:535` has `// echo $best, ' ', $minErr, PHP_EOL;`. Should be removed or use proper logging.

---

## 3. Naming

### Strengths

- **Consistent conventions**: Methods like `toSi()`, `toSiBase()`, `isSi()` follow clear patterns.
- **Descriptive method names**: `removePrefixes()`, `combineSequential()`, `combineConvergent()` are self-documenting.
- **Clear property names**: `asciiSymbol` vs `unicodeSymbol` distinction is clear.

### Minor Suggestions

- `FloatWithError` - consider `MeasuredValue` or `ValueWithUncertainty` to align with metrology terminology.
- `DimensionUtility::explode()`/`implode()` - these are PHP array functions; consider `parse()`/`format()` or `decompose()`/`compose()`.
- `getConversionByTerms()` in Converter - "by terms" is vague; maybe `getConversionByUnitTermPairs()`.

---

## 4. Developer Experience (DX)

### Strengths

- **Multiple API entry points**: Can use `new Length(5, 'm')`, `Quantity::create(5, 'm')`, or `Length::parse('5 m')`.
- **Fluent chaining**: `$force->to('N')->toSi()->simplify()` reads naturally.
- **Static `convert()` method**: Quick conversion without object instantiation.
- **PhysicalConstant with IDE completion**: `PhysicalConstant::speedOfLight()` is discoverable.
- **Unicode support**: Proper handling of symbols like `°`, `µ`, superscript exponents.

### Potential Improvements

- **Error messages could include more context**: When conversion fails, showing the dimension mismatch (e.g., "Cannot convert 'kg' (M) to 'm' (L)") would help.
- **Quantity arithmetic readability**: `$a->mul($b)->div($c)` is less intuitive than `$a * $b / $c`. PHP doesn't support operator overloading, but a helper for expressions could help.
- **Parts API complexity**: `getPartsConfig()`, `fromParts()`, `toParts()` have some edge cases (carry logic). Consider simplifying or documenting heavily.

---

## 5. Utility/Usefulness

### Strengths

- **Comprehensive SI coverage**: All 7 SI base units plus derived units.
- **Multiple measurement systems**: SI, Imperial, US, Nautical, Astronomical, Typography.
- **Real-world constants**: Physical constants from CODATA.
- **Error propagation**: `FloatWithError` tracks uncertainty - valuable for scientific applications.
- **Practical operations**: Add, subtract, multiply, divide quantities with automatic dimension checking.

### Nice-to-Haves for Future

- **Currency dimension**: Noted as reserved (`C`) but not implemented.
- **Unit formatting with quantity**: e.g., `$length->format('%0.2f %s')` for custom output.
- **Comparison operators**: `$a->greaterThan($b)` with unit conversion.

---

## 6. Additional Observations

### Code Quality

- Docblocks are thorough and consistent.
- Test coverage appears comprehensive based on Examples/ directory with real-world physics scenarios.
- The `Equatable` trait promotes consistent equality checking.

### Performance Considerations

- Lazy initialization of registries is good.
- Conversion path caching prevents repeated graph traversal.
- Consider caching parsed DerivedUnits for frequently-used expressions.

### Documentation

- Inline documentation is excellent.
- External documentation (README, user guide) would complete the package.

### API Consistency

- Most methods that return new instances are named consistently (`to()`, `toSi()`, `expand()`, `simplify()`).
- The `isSi()` hierarchy (Unit → UnitTerm → DerivedUnit → Quantity) is clean.

### Minor Issues

- `Prefix.php:70-76`: Regex has spaces in character class `{1, 2}` which is incorrect - should be `{1,2}`.
- Some long methods could be split (e.g., `Quantity::simplify()` at ~80 lines, `DerivedUnit::parse()` at ~90 lines).

---

## Summary

This is a well-designed, thoughtfully-implemented package. The architecture handles the inherent complexity of physical quantities elegantly. The dimension-based type system catches errors at runtime that would be silent bugs in naive implementations.

### Priority Items

1. Fix regex bug in `Prefix.php` (`{1, 2}` → `{1,2}`)
2. Address or track TODO in `UnitTerm.php:302`
3. Remove commented debug code in `Converter.php:535`

### Polish Items

1. Reducing static coupling for testability
2. Breaking down a few long methods
3. Minor naming refinements
4. External documentation (README, user guide)

### Overall Assessment

**Production-quality code** that demonstrates strong PHP engineering. Ready for initial release with minor fixes.
