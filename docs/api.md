# API

The external contract is versioned under `/api/v1`. Web Inertia and API controllers are separate presentation boundaries but invoke the same actions, policies, and scoped domain rules.

## Authentication And Abilities

Public registration/login endpoints are explicitly rate limited. Authenticated endpoints use Sanctum and the `ApiTokenAbility` enum to distinguish workspace, task, export, attachment, and related read/write capabilities. A valid token without the route ability receives a safe forbidden response.

## Response Contract

- Item success: `data`, with optional `meta`.
- Collection success: `data`, `links`, and pagination `meta` when paginated.
- Empty mutation: documented success status without raw model serialization.
- Error: stable machine `code`, localized safe `message`, `errors` for validation where relevant, and request/correlation identifier.
- Dates and enums use explicit resource representations. Relationships are included only when intentionally loaded.

API Resources under `app/Http/Resources` control visibility and never query. Raw Eloquent models and internal exception messages are not public contracts. Invalid content types/malformed JSON, unauthenticated, forbidden, not-found, conflict, rate-limited, and unexpected failures use the centralized exception/response factory boundary.

Unversioned compatibility, if present in route output, must call the same implementation and carry explicit compatibility intent; no new consumer should use it. The complete API behavior and ability matrix is covered under `tests/Feature/Api`.
