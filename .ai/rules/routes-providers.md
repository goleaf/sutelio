---
paths:
    - '{routes/web.php,app/Providers/AppServiceProvider.php}'
---

# Routes Providers

## Isolate locale rate limiting by actor

The locale route uses the named locale limiter. Web requests are keyed by authenticated user or hashed session, while the app-local NativePHP Android/iOS bridge is unlimited because every request is private loopback traffic; never replace this with a numeric IP-only throttle.
