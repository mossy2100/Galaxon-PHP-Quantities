# Documentation Reorganization: Manual vs Reference

Saved from plan mode on 2026-02-15. Revisit when ready.

## Context

The documentation currently mixes explanatory "how to use this" content with API reference content in the same files. The goal is to separate these so that:
- **Manual/** pages contain what users should read — concepts, workflows, how-tos
- **Reference/** pages are stripped to pure API docs — signatures, parameters, return types, brief descriptions

The user has already:
- Restructured the directory into `docs/Manual/` and `docs/Reference/`
- Created three Manual pages: `CustomQuantityTypes.md`, `Examples.md`, `SupportedUnits.md`

## New Manual Page: `GettingStarted.md`

A single new guide page covering the essential workflow for using the package. Content extracted from `Quantity.md` (Overview, Key Features, Usage Examples) and other Reference files, consolidated into one readable document.

### Sections

1. **Creating Quantities** — constructors, `Quantity::create()`, parsing from strings
2. **Unit Conversion** — `to()`, `toSi()`, `toSiBase()`, `autoPrefix()`, `simplify()`
3. **Arithmetic** — `add()`, `sub()`, `mul()`, `div()`, `pow()`, `inv()`, how multiplication/division produces new quantity types (dimensional analysis)
4. **Comparison** — `compare()`, `approxEqual()`, sorting
5. **Formatting** — `format()` specifiers, `__toString()`, ASCII vs Unicode
6. **Parts** — `toParts()`, `fromParts()`, `formatParts()` for decomposed display (e.g. 45° 30' 15")
7. **Loading Unit Systems** — `UnitService::loadSystem()`, which systems are loaded by default
8. **See Also** — links to Examples, CustomQuantityTypes, SupportedUnits, and Reference pages

Source content comes primarily from:
- `Reference/Quantity.md` — Overview, Key Features, Usage Examples sections
- `Reference/System.md` — Usage Examples (Loading Units by System)
- `README.md` — Quick Start and Features sections (reference, don't duplicate)

## Reference File Stripping

Keep Overview paragraphs in all Reference files — every class needs at least a brief explanation of what it is and what it's for. Remove only **Key Features** bullet lists and **Usage Examples** sections (tutorial-style content that belongs in the Manual).

### `Reference/Quantity.md`
- Remove: **Key Features** list under Overview
- Remove: **Usage Examples** section at end (Unit Conversion, Arithmetic, Comparison subsections)
- Keep: Overview paragraphs, all property/method/constructor signatures with parameters, returns, throws, inline examples

### `Reference/PhysicalConstant.md`
- Remove: **Key Features** list under Overview
- Remove: **Usage Examples** section at end (Using Constants in Calculations, Looking Up Constants by Symbol)
- Keep: Overview paragraphs, all method signatures with returns

### `Reference/QuantityType/Angle.md`
- Remove: **Usage Examples** section at end
- Keep: Overview (with feature list — it's brief and describes what the class provides), Constants table, all method signatures, parts section

### `Reference/System.md`
- Remove: **Usage Examples** section (Basic Usage, Checking Unit Systems, Loading Units by System, Working with Cases)
- Keep: Overview, Cases, Cases Summary table

### `Reference/Internal/DerivedUnit.md`
- Remove: **Key Features** list
- Keep: Overview paragraph

### `Reference/Internal/Unit.md`
- Remove: **Key Features** list
- Keep: Overview paragraphs

### `Reference/Internal/Converter.md`
- Remove: **Key Features** list
- Keep: Overview (factual description of the conversion algorithm)

### `Reference/Services/DimensionService.md`
- Remove: **Usage Examples** section at end
- Keep: Everything else (Overview, dimension codes table, method signatures)

### Other Reference files
- Most other Internal/ files have little or no guide content — leave as-is
- QuantityType/ files (other than Angle) are already concise — leave as-is

## README vs Getting Started: Division of Content

**README.md** — the landing page. Its role:
- Brief description and feature highlights (keep existing)
- **Quick Start** with a handful of concise examples (keep existing, trim if needed)
- Remove the extended "Features and Examples" section (this content moves to Getting Started)
- **Complete documentation index** linking to every Manual and Reference file
- Keep: Requirements, Installation, Terminology, Testing, License, Contributing, Support

**GettingStarted.md** — the deep-dive tutorial. Its role:
- Assumes the reader has installed the package and seen the Quick Start
- Teaches each capability in depth with multiple examples
- Replaces the README's "Features and Examples" section content
- Absorbs the Usage Examples stripped from Reference files

This avoids overlap: README gives a taste, GettingStarted teaches thoroughly.

## README Documentation Index

Replace the current Classes tables in README with a complete docs index:

```markdown
## Documentation

### Manual

| Guide | Description |
|-------|-------------|
| [Getting Started](docs/Manual/GettingStarted.md) | Creating quantities, converting, arithmetic, formatting |
| [Supported Units](docs/Manual/SupportedUnits.md) | Complete list of built-in units by quantity type |
| [Examples](docs/Manual/Examples.md) | Real-world physics and engineering calculations |
| [Custom Quantity Types](docs/Manual/CustomQuantityTypes.md) | Extending the package with your own types and units |

### Reference
(keep existing tables for Main Public API, Quantity Types, Services, Internal Classes)
```

## Cross-References

After stripping, update See Also sections in Reference files:
- `Quantity.md` See Also → add `GettingStarted.md`
- `System.md` See Also → add `GettingStarted.md`
- `PhysicalConstant.md` See Also → already links to Examples

## Files Modified

| File | Action |
|------|--------|
| `docs/Manual/GettingStarted.md` | **Create** — new guide page |
| `docs/Reference/Quantity.md` | Strip Key Features, Usage Examples |
| `docs/Reference/PhysicalConstant.md` | Strip Key Features, Usage Examples |
| `docs/Reference/QuantityType/Angle.md` | Strip Usage Examples |
| `docs/Reference/System.md` | Strip Usage Examples |
| `docs/Reference/Internal/DerivedUnit.md` | Strip Key Features |
| `docs/Reference/Internal/Unit.md` | Strip Key Features |
| `docs/Reference/Internal/Converter.md` | Strip Key Features |
| `docs/Reference/Services/DimensionService.md` | Strip Usage Examples |
| `README.md` | Move Features section to GettingStarted, add Manual docs table, full doc index |

## Verification

Review each modified Reference file to ensure:
- Overview paragraph is preserved
- No tutorial/guide prose remains beyond the overview
- All API signatures, parameters, returns, throws are preserved
- Brief descriptive sentences for each item are retained
- See Also sections link to relevant Manual pages
