# ADR 0002: PHP 8.5 Web Runtime With NativePHP 8.4 Compatibility

- Status: accepted with upstream documentation constraint; runtime observation updated 2026-08-17
- Date: 2026-08-16

## Context

The modernization target requests PHP 8.5. Herd has PHP 8.5 available and Laravel 13 supports it. NativePHP Mobile is an intentional repository capability, but official NativePHP v4 documentation states its embedded mobile engine is currently PHP 8.4 and applications must support that runtime.

## Decision

Run web development, CI, static analysis, tests, and deployment on PHP 8.5. Keep application syntax and Composer's supported range compatible with PHP 8.4 until NativePHP publishes a stable embedded PHP 8.5 engine. Do not fake platform compatibility or remove mobile support to satisfy a version string.

## Consequences

PHP 8.5-only syntax/attributes cannot be used in shared application code yet. PHP 8.5 behavior is still tested on the web runtime. The Composer minimum can be raised to 8.5 only after an upstream NativePHP runtime upgrade and full Android/web verification.

## 2026-08-17 Runtime Update

`native:install` now produces the tracked PHP 8.5.9 runtime, and a clean Android 14 emulator installation passed first-run migration and registration. Official NativePHP v4 documentation still says PHP 8.4, so the decision to keep `>=8.4 <8.6` remains in force until the documented support contract catches up; the current generated artifact itself is verified on PHP 8.5.9.
