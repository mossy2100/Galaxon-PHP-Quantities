# Systems of Units

## Overview

The Quantities package includes units from multiple systems of units:

- **SI** - International System of Units (base and derived units)
- **SI Accepted** - Non-SI units officially accepted for use with SI
- **Common** - Widely used units without formal system classification
- **Imperial** - British Imperial system
- **US Customary** - United States customary units
- **Scientific** - Units for scientific applications
- **Nautical** - Units for maritime and aviation
- **Css** - Units for typography and screen layouts

## Loading Additional Systems

By default, only SI, SI Accepted, and Common units are loaded. To use Imperial, US Customary, or other system units,
load them first:

```php
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;

// Load Imperial and US Customary units
UnitRegistry::loadSystem(System::Imperial);
UnitRegistry::loadSystem(System::UsCustomary);

// Now you can use feet, pounds, gallons, etc.
$length = new Length(100, 'ft');
```

