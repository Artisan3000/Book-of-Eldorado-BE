<!--
	Project README for LMS API
	Replaces generic Laravel README with repo-specific developer instructions.
-->

# LMS API (Laravel)

This repository contains the backend API for the LMS (learning management system) built with Laravel 12 / PHP 8.2.

This README explains how to run the application locally, run migrations and seeds, and run the test suite and API tests.

If you're new to this repo, the most important files and folders are:

- `app/Models` — Eloquent models (User, Student, Course, Lesson, Quiz, Project, Discussion, etc.)
- `app/Http/Controllers` — API controllers
- `routes/api.php` — API routes
- `database/migrations` — schema migrations
- `database/seeders` — database seeders (the project includes `DatabaseSeeder.php`)
- `tests/Feature` and `tests/Unit` — automated tests

## Prerequisites

- PHP 8.2
- Composer
- MySQL (or a supported database). You can also use sqlite for quick local testing.
- Node/npm if you need to build frontend assets (not required for API tests)

On Windows, use PowerShell for the commands in this README.

## Quick start — Local development

1. Install PHP dependencies:

```powershell
composer install
```

2. Copy `.env` and generate an app key:

```powershell
copy .env.example .env
php artisan key:generate
```

3. Configure your database in `.env` (DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD). For quick testing, you can use sqlite:

```powershell
# create an sqlite file
New-Item -Path database\database.sqlite -ItemType File -Force
# then set DB_CONNECTION=sqlite and DB_DATABASE=database/database.sqlite in .env
```

4. Run migrations and seeders:

```powershell
php artisan migrate
php artisan db:seed
```

If you want to run only the newly added pivot-table migrations (from this project):

```powershell
php artisan migrate --path="database/migrations/2025_10_25_000000_create_lesson_completions_table.php"
php artisan migrate --path="database/migrations/2025_10_25_000001_create_quiz_completions_table.php"
```

5. Start the dev server:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

6. Open the API at `http://127.0.0.1:8000/api`.

## Running tests

This project includes PHPUnit tests. Run them with:

```powershell
./vendor/bin/phpunit
# or, if a composer script exists:
composer run test
```

To run a single test file:

```powershell
./vendor/bin/phpunit tests/Feature/SomeFeatureTest.php
```

If you'd like to isolate database tests, use sqlite in memory by setting in `phpunit.xml` or temporarily in `.env.testing`.

## Seeding test data

The repository contains `database/seeders/DatabaseSeeder.php`. Run:

```powershell
php artisan db:seed
```

If you want to create or add seeds specifically for demo data (courses, lessons, completions, certificates), add a seeder class under `database/seeders` and call it from `DatabaseSeeder`.

Example: create a quick seeder and run it

```powershell
php artisan make:seeder DemoDataSeeder
# edit database/seeders/DemoDataSeeder.php to insert demo rows
php artisan db:seed --class=DemoDataSeeder
```

## API usage notes

- Authentication: API uses Laravel Sanctum for token-based authentication. To call protected endpoints, include an `Authorization: Bearer <token>` header.
- Admin routes: routes under `/api/admin/...` require an authenticated user with role `admin` (see `App\Http\Middleware\Role`).

Example curl (PowerShell) for admin overview endpoint:

```powershell
curl -H "Authorization: Bearer <ADMIN_TOKEN>" http://127.0.0.1:8000/api/admin/student/12/overview
```

## Troubleshooting

- If you see `Target class [role] does not exist.` in logs, clear route/config caches and restart the serve process:

```powershell
php artisan config:clear; php artisan route:clear; php artisan cache:clear
```

- If you see SQL errors about missing tables, ensure you ran migrations (`php artisan migrate`) and that the database configured in `.env` is the one you expect.

## Developer tips

- Use `php artisan tinker` to quickly interact with models.
- Use `php artisan route:list -v` to inspect middleware and route bindings.

## API testing & Postman

If you have a Postman collection, import it and set an environment variable `baseUrl` to `http://127.0.0.1:8000/api` and an `adminToken` or `userToken` for auth.

---

If you want, I can also add:
- a `docs/API_TESTING.md` with endpoint examples and expected responses (I will create this now),
- sample Postman/Insomnia collection,
- or seeders that populate demo data used in the README examples.
