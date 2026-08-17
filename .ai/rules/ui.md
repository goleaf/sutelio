---
paths:
    - 'resources/js/components/ui/**'
---

# Ui

## Keep boxed controls visible with one-hue gradients

Boxed Button variants use statically discoverable Tailwind 4 `bg-linear-to-br` ramps. `ghost` intentionally shares the visible outlined surface with `outline`; `link` remains unboxed. Shared text-entry/select/checkbox/OTP primitives use the subtle Sutelio orange surface ramp. Preserve keyboard focus, disabled/loading, reduced-motion, forced-colors, and coarse-pointer behavior.
