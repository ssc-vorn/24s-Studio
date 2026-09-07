# UX/UI and delivery audit

**Review date:** 7 September 2026
**Scope:** the authenticated CMS builder, media library, public-page renderer, authentication shell, quality gates, and repository readiness. This is a static code review; it does not substitute for task-based usability sessions with editors or visitors.

## Executive summary

The project has a solid technical base for a versioned CMS: content is tenant-scoped, autosave has optimistic-concurrency handling, and the builder has drafting, review, approval, publishing, preview, undo, and responsive-preview concepts. The experience is not yet ready for real editorial teams because the entry point is a starter dashboard, there is no visible page-management flow, and important state-recovery and accessibility feedback is hidden or missing.

The next release should focus on the **editor journey**, not additional section types: get an editor from “sign in” through “find/create page”, “edit safely”, “review/publish”, and “verify the public page” without relying on a manually constructed URL.

## What currently works

| Area | Evidence | UX value |
| --- | --- | --- |
| Versioned workflow | The builder exposes draft, review, approved, and published states with matching actions. | Establishes the right governance model for agency content work. |
| Save safety | Autosave is debounced, tracks revision conflicts, keeps local changes after an error, and warns before a browser unload. | Reduces accidental loss during normal editing. |
| Responsive editing | The builder exposes a viewport switcher and has a dedicated preview route. | Makes responsive review part of the authoring workflow. |
| Basic media management | The media API supports search, MIME filtering, upload, alt text, update, and delete operations. | Supports reusable content assets rather than one-off uploads. |
| Tenant and authorization foundations | Builder and API controllers check organization scope and policies. | Keeps agency/client work separated. |

## Priority findings and next actions

### P0 — unblock a usable editor journey (do first)

1. **Replace the starter dashboard with a CMS home/page index.** The current dashboard only says that the user is logged in, while the builder and media pages require organization/page/version UUIDs in the URL. Create an organization switcher and a page list with status, last updated, owner, “new page”, edit, preview, duplicate/version history, and media entry points. This is the biggest conversion and discoverability gap.
2. **Make save failure and conflict recovery actionable in the builder.** The toolbar shows only a small status label at `sm` widths and does not render the detailed `saveError`; a conflict tells the user to reload but provides no Reload/Retry/Copy-local-changes controls. Use a persistent, keyboard-focusable error banner with explicit recovery actions. Keep a local draft snapshot before reload.
3. **Add explicit navigation protection.** The app warns only on browser/tab close. Going Back in the builder calls `router.visit('/dashboard')` without a confirm/discard/save decision, so an editor can leave a dirty page. Intercept internal navigation and provide Save and leave / Leave without saving / Stay.
4. **Resolve the committed README merge-conflict markers.** They make onboarding and project identity unreliable and are a release-readiness signal. Replace the starter Laravel text with a real Studio setup, UX workflow, test, and deployment guide.

### P1 — make the workflow understandable and accessible (next sprint)

5. **Turn workflow transitions into an intelligible review experience.** “Submit for review”, “Approve”, and “Publish” have no confirmation, audit context, reviewer assignment, comment/request-changes path, or success feedback. Add a side panel/modal explaining the effect, a review checklist, optional comment, actor/timestamp history, and a clear published URL with “Open live page”.
6. **Make the builder responsive and keyboard-operable.** It enforces a `min-h-[620px]` three-pane desktop layout; panel collapse/drawer behavior and focus management are absent. On tablets/mobile, use a canvas-first layout with Layers/Inspector in accessible drawers. Add visible `:focus-visible` styles, keyboard shortcuts/help, and announce save/workflow state with an `aria-live` region.
7. **Make destructive operations recoverable.** Deleting a section uses `window.confirm`, which is inconsistent with the UI and cannot explain nested impact. Replace it with an accessible dialog that names affected sections and offers Undo after deletion. Use equivalent confirmation and in-context feedback for media deletion.
8. **Finish media-library user feedback.** Provide upload progress, accepted formats/size near the drop target, image thumbnail loading/error states, empty/search-empty states, pagination/load more, edit-alt-text confirmation, and insertion success feedback. Require or prominently prompt for meaningful alt text; allow an explicit “decorative” option rather than silently accepting missing alt text.

### P2 — public experience, design system, and quality (plan after P0/P1)

9. **Build the public-page experience around semantics and SEO.** The renderer outputs arbitrary sections directly in `<main>` without a shared site shell. Add site navigation/footer, language metadata, canonical URL, social metadata, a single logical H1 per page/template, skip link, meaningful landmarks, 404/empty-page handling, and image dimensions/lazy loading. Test each section at mobile and desktop breakpoints.
10. **Establish a cohesive visual language.** Authenticated pages still use Laravel starter gray styling while CMS screens use slate styling; component casing is also duplicated (`Pages` and `pages`). Define tokens for type scale, spacing, surfaces, feedback colors, elevation, and focus, then refactor shared primitives before adding more bespoke screens. Select Khmer-capable typography and verify actual Khmer content rather than placeholder English copy.
11. **Add observable product quality gates.** Add browser-level critical-path tests (login → select org → create/edit → autosave → review/publish → public verify), automated accessibility checks (axe), mobile visual regression snapshots, and performance budgets for public pages. Track time-to-first-edit, autosave failure/conflict rates, publish success, and accessibility defects.

## Recommended delivery sequence

### Sprint 1: editorial entry and safety

- CMS home/page index, organization context, create-page flow, and navigation to builder/media.
- Persistent save/conflict recovery banner plus internal-navigation guard.
- Replace README with the actual project handbook and remove merge-conflict markers.
- **Acceptance:** a new editor can find a page, edit it, deliberately handle a simulated save failure, and return to the page list without data loss.

### Sprint 2: review and accessible builder

- Workflow confirmation/history/commenting and live-page verification.
- Responsive panel/drawer behavior, focus states, keyboard path, and live announcements.
- Accessible delete dialog plus undo.
- **Acceptance:** keyboard-only editor can add, select, edit, reorder, delete/undo, save, submit, and return; reviewer can identify exactly what they are publishing.

### Sprint 3: media and public page quality

- Media upload/picker states, alt-text workflow, empty/error/pagination states.
- Public site shell, metadata, semantic templates, and core-web-vitals asset handling.
- Critical-path E2E/a11y/visual checks and baseline analytics.
- **Acceptance:** a published page passes automated a11y checks with no critical violations and has defined metadata, mobile layout, and image behavior.

## Validation plan before calling the product editor-ready

1. Test five real editorial tasks with at least three agency editors: create page, change hero, replace image and alt text, recover from lost network, and submit/approve/publish.
2. Measure task completion, time, misclicks, support requests, and System Usability Scale score; target at least 90% unassisted completion for the five tasks.
3. Test keyboard-only, 200% zoom, narrow mobile/tablet widths, slow/offline network, and concurrent editing.
4. Run automated PHP, type, lint, build, E2E, accessibility, and visual-regression checks in CI; block release on critical a11y failures or unrecovered save conflicts.

## Implementation notes

- Do not add more content blocks until P0 entry/safety flow is complete; otherwise feature breadth will worsen editor discoverability.
- Preserve the existing server-side tenant/policy checks when adding the new CMS index and actions; UI hiding is not authorization.
- Use a shared accessible dialog/toast/banner primitive so save, workflow, destructive, upload, and validation feedback behave consistently.
