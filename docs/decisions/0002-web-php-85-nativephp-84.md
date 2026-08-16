# ADR 0002: PHP 8.5 Web Runtime With NativePHP 8.4 Compatibility

- Status: accepted with upstream constraint
- Date: 2026-08-16

## Context

The modernization target requests PHP 8.5. Herd has PHP 8.5 available and Laravel 13 supports it. NativePHP Mobile is an intentional repository capability, but official NativePHP v4 documentation states its embedded mobile engine is currently PHP 8.4 and applications must support that runtime.

## Decision

Run web development, CI, static analysis, tests, and deployment on PHP 8.5. Keep application syntax and Composer's supported range compatible with PHP 8.4 until NativePHP publishes a stable embedded PHP 8.5 engine. Do not fake platform compatibility or remove mobile support to satisfy a version string.

## Consequences

PHP 8.5-only syntax/attributes cannot be used in shared application code yet. PHP 8.5 behavior is still tested on the web runtime. The Composer minimum can be raised to 8.5 only after an upstream NativePHP runtime upgrade and full Android/web verification.
