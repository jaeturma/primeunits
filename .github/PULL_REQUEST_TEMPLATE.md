## What & why

<!-- Describe what changed and why. Link any related issue. -->

## How to test

<!-- Steps a reviewer can follow to verify this manually. -->

## Screenshots

<!-- For UI changes, before/after screenshots or a short clip. Delete this section if not applicable. -->

## Checklist

- [ ] `composer lint:check` passes
- [ ] `npm run lint:check` passes
- [ ] `npm run format:check` passes
- [ ] `npm run types:check` passes
- [ ] `php artisan test` passes (and I added/updated tests for this change)
- [ ] `npm run build` succeeds
- [ ] If this touches categories/classifications/spec fields, it reuses the existing `Category → Classification → Spec Field` model rather than adding a new parallel structure

## Notes for reviewers

<!-- Anything you're unsure about, alternatives you considered, or follow-up work left out of scope. -->
