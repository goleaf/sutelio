# Sutelio Full Brand Rename Design

## Status

Drafted on 2026-08-17 after an interactive naming and visual-identity review. The user approved the third clean-S direction and explicitly requested a complete rename rather than a display-name-only update. This document defines the proposed rename boundary, compatibility consequences, and verification contract for final review. A separate implementation plan will sequence exact file changes after this specification is confirmed.

## Objective

Replace the active Xiaomi Mimo identity with Sutelio across the web application, NativePHP Mobile packages, generated and source-controlled brand assets, configuration defaults, user-facing copy, canonical documentation, tests, build evidence, repository metadata, and deliverable naming.

The rename will be performed in place. It does not create a replacement application architecture, branch, or repository history. Laravel, Inertia, Vue, NativePHP, SQLite, the domain model, routes, authorization, and user data remain the same unless an identifier change described here requires a build or installation migration.

## Selected Identity

### Product Name

- Canonical product and project name: `Sutelio`.
- The wordmark is always written as `Sutelio` with one capital letter and no internal color change.
- The final `o` is never orange or otherwise isolated.
- Active user-facing and canonical technical references use Sutelio. Historical append-only progress entries and dated audits retain Xiaomi Mimo where required to preserve truthful evidence of what was built and verified at that time.

### Logo Mark

The selected mark is the third clean-S concept:

- a cobalt rounded-square application tile;
- one centered orange circle;
- one centered solid ivory capital `S` inside the circle;
- no stripe, cut, line, gradient, shadow, secondary glyph, or color break inside the `S`;
- no orange letter inside the `Sutelio` wordmark.

The prototype palette becomes the exact brand palette:

| Role          | Value     | Use                                                         |
| ------------- | --------- | ----------------------------------------------------------- |
| Brand cobalt  | `#123C8B` | App-icon tile, primary brand field, dark brand presentation |
| Deep cobalt   | `#0A285F` | High-contrast wordmark and dark-background support          |
| Signal orange | `#FF6038` | Circle behind the `S` and restrained external brand accents |
| Warm ivory    | `#FFF8E9` | Solid `S`, splash background, light brand presentation      |

The final master mark must use SVG path geometry for the `S`; it must not depend on an SVG `<text>` node, a system font, a runtime webfont, or raster tracing. The prototype is a visual reference, not the production vector source.

### Geometry And Scaling

- The tile uses a rounded-square silhouette and retains clear space around the orange circle.
- The orange circle is centered and has a diameter equal to 70 percent of the tile width.
- The ivory `S` is optically centered within the circle and remains legible at 16 CSS pixels.
- Android adaptive foreground content remains inside the platform safe zone so circle or squircle masks do not crop the mark.
- A monochrome Android variant preserves the `S` silhouette for themed icons without encoding status through color.
- The standalone horizontal lockup pairs the mark with a one-color `Sutelio` wordmark. The wordmark uses the existing Instrument Sans family but remains text in UI contexts and outlined vector geometry in exported brand artwork.

## Presentation Contract

### Web

- Replace the current Laravel-derived application mark in shared Vue logo components.
- Replace `favicon.svg`, `favicon.ico`, and the Apple touch icon with Sutelio assets derived from the same master geometry.
- Update the document title, application metadata, accessible logo labels, sidebar tooltips, authentication surfaces, and any other active product-name presentation.
- Light surfaces use the cobalt mark tile and a deep-cobalt or current semantic foreground wordmark.
- Dark surfaces use the same mark tile and an ivory wordmark.
- The existing Warm Precision application system remains fixed. The brand palette does not create a runtime theme family or recolor unrelated status, destructive, success, focus, or data-visualization semantics.

### Splash

- Use a warm-ivory splash background.
- Center the complete Sutelio mark with generous safe-area padding.
- Place a one-color deep-cobalt `Sutelio` wordmark below the mark when the platform launch format permits it.
- Do not add a tagline, loading percentage, animation dependency, gradient, or legacy name.
- Android 12+ system splash configuration and generated NativePHP launch assets must visually agree with the static reference.

### Android And iOS

- Generate Android adaptive foreground, background, monochrome, legacy launcher, round launcher, and 512-pixel store artwork from the master vector.
- Generate the complete iOS AppIcon asset set and launch artwork from the same master vector.
- Generated native projects remain ignored build products, but every regeneration must deterministically reproduce the accepted Sutelio identity.
- APK and application labels use `Sutelio` without Xiaomi Mimo fallback text.

## Complete Rename Scope

### Application And Package Identity

- Laravel application default name: `Sutelio`.
- NativePHP service/application and app-store names: `Sutelio`.
- Android application ID and namespace: `com.goleaf.sutelio`.
- iOS bundle identifier: `com.goleaf.sutelio`.
- Native deep-link scheme: `sutelio`; the verified HTTPS host remains environment-specific and is not replaced with an invented domain.
- Composer root package name: `goleaf/sutelio`, with Sutelio-specific description and keywords replacing the current starter-kit metadata.
- npm root package name: `sutelio`; the package remains private.
- Backup download prefix: `sutelio-backup-`; test-only temporary prefixes use `sutelio-test-`.
- Published Android debug deliverable name: `sutelio-android-debug.apk`; platform-internal Gradle output may retain its generated filename before the verified publication copy is made.

### Source, Copy, And Documentation

- Replace active product-name strings in Vue, Blade bootstrap metadata, PHP configuration, English/Lithuanian/Russian catalogs, mail and notification copy, tests, README, changelog, current-state documentation, deployment/operations documentation, and current canonical requirement evidence.
- Preserve translation key parity and complete-sentence grammar in all three locales.
- Update tests that intentionally assert product identity; do not weaken unrelated architecture, localization, security, or behavior assertions.
- Search source-controlled, first-party active files case-insensitively for `Xiaomi Mimo`, `xiaomi-mimo`, `xiaomimimo`, and `com.goleaf.xiaomimimo` before delivery.
- Do not rewrite dated historical audits, old append-only progress evidence, or quoted historical commands when that would falsify prior facts. Add a clear successor note where an active reader could otherwise mistake the historical name for the current product.

### Repository And Local Project Identity

- Rename the existing GitHub repository in place from `goleaf/xiaomi-mimo` to `goleaf/sutelio`; do not create a replacement repository or lose history.
- Update the local Git remote after the in-place repository rename and verify fetch/push on `main`.
- Rename the local project directory and Herd site from `xiaomi-mimo` to `sutelio` only after all source changes, commits, pushes, and build verification succeed from the original checkout.
- Update active local URLs and documentation from `xiaomi-mimo.test` to `sutelio.test` after the Herd rename is verified.
- Repository and local-directory renames are the final operational step because they change the active workspace path used by tooling.

## Compatibility And Data Consequences

### Web And SQLite

The domain schema and SQLite data do not need a product-name migration. Workspace, project, task, membership, activity, notification, and preference data remain unchanged. Environment configuration and cached application metadata must be refreshed after the rename.

### Mobile Package Boundary

Changing `com.goleaf.xiaomimimo` to `com.goleaf.sutelio` creates a new Android application sandbox and a new store identity. The new APK cannot update the old package in place, and it cannot directly read the old package's private SQLite database. The same bundle-identity boundary applies to iOS application storage and store identity.

The implementation must therefore:

- treat Sutelio as a clean mobile install;
- preserve the old Xiaomi Mimo package until the Sutelio APK has passed installation and launch verification;
- avoid claiming automatic mobile data migration;
- uninstall the old debug package only after the new package and database path are independently verified, or leave both installed when comparison is useful;
- document that production users would require a separately designed export/import or signed migration release if old mobile data must move across package identities.

### Repository Rename Boundary

An in-place GitHub rename normally preserves history and may provide redirects, but the implementation must verify the observed remote behavior instead of assuming it. If repository-rename authority or network access is unavailable, the code and brand delivery can complete while the exact external blocker is recorded; a new repository must not be created as a workaround.

## Implementation Boundaries

The work is divided into independently verifiable units:

1. **Master identity assets** — one authoritative vector mark and horizontal lockup, plus deterministic raster exports.
2. **Web consumers** — shared Vue logo components, browser icons, application metadata, accessible labels, and active user copy.
3. **Technical identity** — Laravel, Composer/npm, NativePHP, Android/iOS, deep-link, backup, test-fixture, and artifact names.
4. **Native generation** — regenerate ignored mobile projects, verify exact produced asset catalogs and package metadata, then build the APK.
5. **Canonical evidence** — update current documentation, requirement/compliance references, deployment evidence, and append-only progress without rewriting history.
6. **External rename** — rename the existing GitHub repository and local Herd checkout only after the verified code delivery is safely pushed.

No backend domain action, API response shape, workspace authorization rule, database schema, email-verification behavior, or unrelated UI workflow changes as part of this brand phase.

## Failure And Recovery

- SVG/raster generation must fail if expected dimensions, alpha behavior, or source files are missing; it must not silently keep a legacy icon.
- Native generation must stop if the resulting application ID, label, icon resources, or splash resources still contain the legacy identity.
- A failed APK install, launch, or first migration leaves the old package intact for comparison and recovery.
- A failed GitHub rename leaves `origin` unchanged and records the blocker; it never creates or force-pushes a replacement repository.
- A failed local directory/Herd rename occurs only after pushed commits and can be rolled back by restoring the original directory and site link.
- Git commits remain coherent and revertible. Generated native output, local environment values, databases, debug APKs, and signing material remain outside source control unless the repository already tracks an authoritative source file.

## Verification Contract

### Identity And Asset Checks

- Automated tests assert the canonical `Sutelio` display name, one-color wordmark contract, and expected SVG color/geometry tokens.
- Source scans reject active legacy product and package identifiers outside an explicit historical allowlist.
- SVG parsing and raster inspection verify dimensions, nonempty artwork, expected colors, favicon readability, alpha/channel behavior, and absence of accidental text/font dependencies in the master mark.
- Browser checks cover the shared sidebar/header/auth logo, document title, favicon, accessible name, light/dark appearance, 200 percent zoom, and forced-colors fallback.

### Native Checks

- NativePHP configuration tests assert `com.goleaf.sutelio`, `Sutelio`, and normalized platform configuration.
- Regenerate the native project and build a fresh Android debug APK.
- Independently inspect the APK with Android build tools for package ID, label, version, minimum/target SDK, adaptive/monochrome icon resources, splash resources, signature, ZIP alignment, archive integrity, and host-database exclusion.
- Clean-install Sutelio on the Android 14 emulator, launch cold, complete first-run migration, verify SQLite integrity, inspect login/registration rendering, and review current Laravel/logcat errors.
- Capture visual evidence that launcher, system splash, and in-app logo match the accepted third clean-S concept.
- Verify that the old and new package identities are not confused during ADB targeting or cleanup.

### Application Quality

- Run focused identity/configuration/frontend regressions after each implementation unit.
- Before delivery run Pint, Larastan, the full sequential Pest suite, isolated parallel Pest, frontend tests, Vue type checking, ESLint, Prettier verification, npm audit, Composer validation/audit, production web and Android Vite builds, isolated migration/seeding, cache/route/view checks, browser checks, APK inspection, and emulator verification.
- Inspect the complete and staged diffs, scan for secrets and generated output, commit coherent phase-owned slices on `main`, and push normally without history rewriting.

## Success Criteria

The rename is complete only when:

- every active interface and build identifies the product as Sutelio;
- the selected solid ivory `S` in an orange circle on cobalt is used consistently across web, favicon, app icons, and splash;
- the `Sutelio` wordmark is one color and the `S` contains no stripe or internal line;
- Android reports package `com.goleaf.sutelio` and label `Sutelio`;
- the freshly installed emulator build launches and passes integrity/log review;
- active first-party source and canonical documentation contain no unintended Xiaomi Mimo identity;
- the existing repository is renamed in place without losing history, or the exact external authority blocker is recorded;
- the final committed and pushed state is factual, reproducible, and clean.

## Non-Goals

- No feature redesign, authentication change, email-verification change, domain-model change, database migration, new relational database, Redis requirement, frontend framework change, or dependency upgrade.
- No automatic cross-package mobile data migration is claimed or implemented in this branding phase.
- No runtime theme-family selector or broad recoloring of existing semantic UI states.
- No deletion or rewriting of historical evidence that truthfully refers to Xiaomi Mimo at the time it was recorded.
