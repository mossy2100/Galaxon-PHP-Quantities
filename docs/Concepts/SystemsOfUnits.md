# Systems of Units

---

## Overview

The Quantities package includes units from multiple systems of units, which are represented by the `UnitSystem` enum:

| Enum Value                | Description                                              | Loaded by default |
| ------------------------- | -------------------------------------------------------- | ----------------- |
| `UnitSystem::Si`          | International System of Units (base and derived)         | ✅                |
| `UnitSystem::SiAccepted`  | Non-SI units officially accepted for use with SI         | ✅                |
| `UnitSystem::Common`      | Widely used units that don't belong to a specific system | ✅                |
| `UnitSystem::Metric`      | Non-SI metric units (liters, hectares, cups, etc.)       |                   |
| `UnitSystem::Imperial`    | British Imperial units                                   |                   |
| `UnitSystem::UsCustomary` | United States customary units                            |                   |
| `UnitSystem::Scientific`  | Units for scientific applications                        |                   |
| `UnitSystem::Nautical`    | Units for maritime and aviation                          |                   |
| `UnitSystem::Css`         | Units for typography and screen layouts                  |                   |
| `UnitSystem::Financial`   | Currencies                                               |                   |
| `UnitSystem::Custom`      | Default for user-defined units                           |                   |

The purpose of the enum is to provide a mechanism for distinguishing SI, English, and other units.

---

## See Also

- **[Units](Units.md)** — Complete unit reference, showing which system each unit belongs to.
- **[UnitSystem](../Reference/UnitSystem.md)** — Enum reference for unit system values.
- **[UnitService](../Reference/Services/UnitService.md)** — Service for loading and managing units.
- **[Customization](../WorkingWithQuantities/Customization.md)** — Adding custom units and assigning them to systems.
