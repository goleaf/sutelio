---
paths:
    - 'resources/js/{components/project/**,components/onboarding/ProjectStep.vue,pages/projects/**}'
---

# Projects

## Use the shared project icon registry and picker

All user-facing project icon selection must compose ProjectIconPicker; do not add free-text icon fields or local option arrays. Render persisted project icons through ProjectIcon so onboarding previews, project cards, and headers share the curated EN/LT/RU labels and safe folder fallback.
