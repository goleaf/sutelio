---
paths:
    - 'scripts/apply-native-brand.mjs,config/nativephp.php'
---

# Scripts

## Reapply NativePHP bundle and log hardening

Fresh NativePHP generation restores verbose Android request, header, cookie, response, and CSRF logging and may copy repository-local tooling into the Laravel bundle. Keep cleanup exclusions and the deterministic brand:native source sanitizer covered by tests, then rebuild and verify the APK archive plus process-scoped Logcat before delivery.
