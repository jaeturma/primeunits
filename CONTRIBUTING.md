# Contributing to PrimeUnits

Thanks for your interest in improving PrimeUnits. This document covers how to get set up, the conventions the codebase follows, and what's expected before opening a pull request.

## Getting started

Follow the [setup instructions in the README](README.md#setup) to get a local environment running, then create a branch off `main`:

```bash
git checkout -b your-name/short-description
```

## Code style

- **PHP** follows [Laravel Pint](https://laravel.com/docs/pint) (Laravel preset). Run `composer lint` to auto-fix, or `composer lint:check` to verify without changing files.
- **TypeScript/React** follows ESLint + Prettier. Run `npm run lint` and `npm run format` to auto-fix, or `npm run lint:check` and `npm run format:check` to verify.
- Keep PHP strictly typed and prefer Eloquent query scopes over duplicating query logic across controllers (see `Listing::scopeSearch()` for an example of logic shared between the homepage search and the listings catalog).
- Don't introduce new taxonomy tables or duplicate concepts (category/classification/spec field) without checking `app/Models/Category.php` and `database/seeders/CategorySeeder.php` first — the existing structure is meant to cover new vehicle/equipment types via seeded data, not new tables.

## Tests

New behavior should come with a Pest feature test. Run the full suite before opening a PR:

```bash
php artisan test
```

To run a single test file or filter by name:

```bash
php artisan test tests/Feature/MarketplaceSearchTest.php
php artisan test --filter="classification filter"
```

Note: the suite currently has a handful of known-failing tests unrelated to most feature work (auth/2FA flow, RBAC permission counts, a couple of stale SEO/analytics fixtures). If your change doesn't touch those areas, you don't need to fix them, but please don't add new failures on top of them.

## Before opening a pull request

Run everything CI will run:

```bash
composer lint:check
npm run lint:check
npm run format:check
npm run types:check
php artisan test
npm run build
```

(Or `composer ci:check` / `composer test`, which bundle most of these.)

## Commit messages

Keep the summary line short and focused on *why* the change was made, not a restatement of the diff. Reference an issue number when applicable.

## Pull requests

- Target `main` unless a maintainer asks otherwise.
- Describe what changed and why, and call out any manual testing you did (screenshots for UI changes are appreciated).
- Keep PRs scoped to one concern — separate refactors from feature work where possible.

## Reporting bugs / proposing features

Open a GitHub issue with steps to reproduce (for bugs) or the problem you're trying to solve (for feature requests). For anything touching the category/classification/search taxonomy, please describe how it fits the existing `Category → Classification → Spec Field` model before proposing new structures.
