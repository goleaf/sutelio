# ADR 0001: Preserve Inertia And Vue; Do Not Add Livewire

- Status: accepted
- Date: 2026-08-16

## Context

The repository is a mature Laravel 13/Inertia 3/Vue 3/TypeScript application. The repository-level `AGENTS.md` explicitly preserves that stack and forbids Livewire and Volt. A generic modernization request also described Livewire 4/Flux work, while its own conflict rules rank the repository instructions first.

## Decision

Preserve Inertia/Vue/Wayfinder/Reka/Tailwind as the only web presentation architecture. Do not install Livewire, Volt, Flux, or Flux Pro. Interpret Livewire-specific requirements as non-applicable with documented reason.

## Consequences

All server-backed interactivity continues through Inertia and typed Vue components. Accessibility, localization, loading/error states, authorization, and testing are improved within the existing system instead of funding a duplicate routing/state/component stack.
