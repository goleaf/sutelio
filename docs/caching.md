# Caching

Laravel's database-backed cache and locks support framework/runtime state. The application currently has no separately cached workspace/product query whose stability and invalidation cost justify caching.

Before adding a product cache, document and test:

- owner and purpose;
- versioned key format;
- workspace, user, role/permission, and locale scope;
- TTL and stale behavior;
- every invalidation trigger;
- stampede/lock behavior;
- cache-unavailable fallback;
- no cross-tenant or cross-locale leakage.

Use cache tags only on a driver that supports them and when operations accept that dependency. `Cache::touch` is non-applicable until an existing cache item legitimately needs sliding expiry. Do not add Redis/Memcached merely to demonstrate caching.
