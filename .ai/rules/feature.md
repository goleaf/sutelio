---
paths:
    - '{app/Http/**,app/Actions/**,app/Concerns/**,lang/*/validation.php,tests/Feature/**}'
---

# Feature

## Keep validation errors human and locale-complete

Every Laravel validation rule template and every first-party request attribute must have parity in lang/en, lang/lt, and lang/ru validation.php. Automatic validators use the shared attributes map; ambiguous fields such as profile name receive a narrow contextual attributes() override. Manual validation translations must pass validation.attributes.* labels, never raw request keys or dynamic machine identifiers.
