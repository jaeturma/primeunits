# PrimeUnits

A Laravel + React/Inertia marketplace for buying, selling, and renting vehicles, motorcycles, EVs, trucks, farm machinery, and heavy equipment in the Philippines.

**Stack:** Laravel 13 (PHP 8.3+), Inertia.js, React 19, TypeScript, Tailwind CSS 4, Vite, Pest.

## Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+ and npm
- SQLite (default) or MySQL/PostgreSQL if you change `DB_CONNECTION`

## Setup

```bash
git clone https://github.com/jaeturma/primeunits.git
cd primeunits

composer install
npm install

cp .env.example .env
php artisan key:generate

# SQLite is the default DB_CONNECTION — create the database file:
touch database/database.sqlite

php artisan migrate
php artisan db:seed   # optional: demo categories, brands, listings, users

npm run build
```

Or run the whole setup in one go with the bundled Composer script:

```bash
composer setup
```

## Running locally

```bash
composer dev
```

This starts the PHP dev server, the queue listener, and the Vite dev server together. The app will be available at the URL in `APP_URL` (default `http://localhost:8000`).

To run the frontend and backend separately instead:

```bash
php artisan serve
npm run dev
```

## Demo accounts

If you ran `php artisan db:seed`, the following accounts are available (password shown, or `password` where not specified):

| Role | Email | Password |
| --- | --- | --- |
| Superadmin | `admin@prime.test` | `super123` |
| Operations manager | `manager@prime.test` | `password` |
| Insurance desk manager | `insurance@prime.test` | `password` |
| Seller (verified) | `seller.cebu@prime.test` | `password` |
| Buyer | `buyer.miguel@prime.test` | `password` |

## Extra demo data (Davao Region)

To browse the marketplace with a larger, more realistic catalog, seed ~100 listings and ~50 rental units spread across every category/rental type and real Davao Region provinces and cities:

```bash
php artisan db:seed --class="Database\Seeders\DavaoListingSeeder"
php artisan db:seed --class="Database\Seeders\DavaoRentalSeeder"
```

Both are safe to re-run (they update existing rows instead of duplicating them) and are intentionally **not** part of `php artisan db:seed`'s default `DatabaseSeeder` chain, since `DemoSeederTest` asserts exact row counts against that chain.

## Testing & code quality

```bash
php artisan test          # Pest test suite
composer lint:check       # Laravel Pint (PHP style)
npm run lint:check        # ESLint
npm run format:check      # Prettier
npm run types:check       # TypeScript
composer test             # config clear + lint check + full test suite
```

## Project structure

- `app/Models/Category.php`, `app/Models/CategorySpecField.php` — the category → classification → spec field taxonomy behind search and listing forms
- `app/Http/Controllers/HomeController.php` — the "Find a Unit" homepage search
- `app/Models/Listing.php` — shared `scopeSearch()` used by both the homepage and the full listings catalog
- `resources/js/pages/welcome.tsx` — the homepage search UI
- `database/seeders/` — demo data (categories, brands, locations, listings, users)
