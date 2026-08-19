---
paths:
    - '{scripts/build-brand-assets.mjs,scripts/apply-native-brand.mjs,resources/brand/**}'
---

# Brand

## Keep Android startup vector and readiness driven

The approved Sutelio mark is the Signal Orange custom ivory ribbon with no cobalt launcher field or font-derived glyph. Android startup must use the tracked animated/vector resources and guarded Compose handoff, never restore full-screen bitmap decoding or a minimum splash duration. Keep EN/LT/RU native strings, the 170 ms readiness exit, and a static final state when platform animations are disabled.
