# Native Brand And Splash Performance Design

Date: 2026-08-19
Status: Approved for implementation

## Problem

The current launcher mark places a font-derived ivory `S` inside a Signal Orange circle and a cobalt outer tile. On Android the cobalt adaptive-icon background reads as a blue outline around the actual mark. The Android in-app splash also decodes a 1080x1920 PNG before it can paint the branded image, remains visible until the first native/WebView content signal, and adds a 300 ms exit fade. Three physical Samsung cold-start measurements on the current installed build were 4,909 ms, 4,903 ms, and 4,870 ms (median 4,903 ms), which makes that extra visual work noticeable.

## Considered Approaches

1. **Replace only the PNG artwork.** This is the smallest visual change, but it retains full-screen bitmap decoding and cannot provide responsive, reduced-motion-aware animation.
2. **Add a Lottie splash package.** This provides rich animation but introduces a dependency, a second asset/runtime contract, and avoidable startup weight for a short native state.
3. **Use deterministic vectors plus the existing Compose and Android splash APIs.** This keeps the brand generator as the source of truth, removes full-screen Android bitmap decoding, supports the Android 12 system splash, and adds no dependency.

The third approach is selected.

## Brand Contract

- The master mark is a Signal Orange disc carrying one custom ivory ribbon path. It contains no font-derived letter, cobalt tile, blue outline, gradient, stroke, or text element.
- Opaque store and launcher canvases use Signal Orange edge to edge with the same ivory ribbon, allowing the operating system to own the final icon mask.
- The monochrome Android resource uses the identical ribbon silhouette.
- The deep-cobalt Sutelio wordmark remains unchanged and continues to pair with the mark in lockups and splash artwork.
- Browser, iOS, Android adaptive, Android monochrome, store, and splash outputs remain deterministic products of `scripts/build-brand-assets.mjs`.

## Android Splash Contract

- Android 12+ uses a short animated-vector version of the mark on the ivory system splash. The animation never determines how long the application waits.
- The in-app handoff uses a native Compose vector instead of decoding the full-screen PNG.
- The visual hierarchy is one orange ribbon mark, the Sutelio name, a short purpose statement, an honest local-workspace loading status, a quiet indeterminate progress sweep, and a local-first privacy note.
- Motion consists of a one-time settle, low-amplitude breathing halo, slowly orbiting signal points, soft status emphasis, and a small progress sweep. Motion never blocks content readiness or introduces a minimum display duration.
- If Android animations are disabled, the final static composition is rendered with no looping motion.
- Native strings are complete in English, Lithuanian, and Russian and follow the device locale because Laravel has not loaded when this screen first appears.
- The splash exits immediately on the existing first-content signal with a 170 ms fade instead of 300 ms.

## Performance And Accessibility Acceptance

- Android startup contains no `BitmapFactory.decodeResource`, full-screen splash bitmap, or artificial minimum delay.
- The first frame remains native and inexpensive; PHP/WebView initialization stays deferred until after that frame.
- The existing first-content and `reportFullyDrawn()` boundary remains truthful.
- The loading copy does not claim fake stages or fake percentages.
- Text remains legible on small phones and tablets, and the composition respects system font scaling, animation disablement, high contrast, and touch-independent screen-reader semantics.
- Physical-device before/after measurements use the same Samsung, package, force-stop cold-start procedure, and at least three samples.

## Test-First Delivery Sequence

1. Lock the new ribbon geometry, no-cobalt icon field, native strings/resources, source patch, reduced-motion handling, and shorter exit in failing Pest contracts.
2. Update the deterministic generator and regenerate all tracked brand outputs.
3. Extend the guarded NativePHP publication script to install the new resources and patch the exact NativePHP 4.2 `MainActivity` template.
4. Run focused brand/native tests, formatting, static analysis, the complete backend/frontend gates, and the production build.
5. Verify browser identity in isolated Chrome DevTools and Playwright, then regenerate NativePHP, build and inspect the APK, and prove the exact artifact on an isolated emulator.
6. Review, commit, push `main`, confirm exact remote equality, resolve exactly one physical Samsung, record the old package state, uninstall only `com.goleaf.sutelio`, install the exact new APK, and verify cold startup and rendered application state.

## Destructive Delivery Boundary

The requested uninstall intentionally removes only the old `com.goleaf.sutelio` private sandbox, including its cookies, database, remembered-email ciphertext, and preferences. No other package or device data may be changed. The uninstall is the final hardware mutation and is permitted only after every source, test, browser, build, emulator, review, publication, and artifact gate succeeds.
