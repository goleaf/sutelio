---
paths:
    - 'lang/*.json'
---

# Lang

## Keep vendor literal translations server-only and complete

Root EN/LT/RU JSON catalogs exist only for installed Laravel packages that call __() with literal or dynamic-literal English keys. Never edit vendor or treat these files as a client translation store. Derive active Fortify/Passkeys keys from installed callers and enforce locale plus placeholder parity when package versions change.
