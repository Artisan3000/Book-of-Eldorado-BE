## Purpose
This file gives brief, targeted guidance so an AI coding agent can be immediately productive in this Laravel app.

## Big picture
- Framework: Laravel (composer.json requires `laravel/framework` ^12) using PHP 8.2.
- API auth: Laravel Sanctum is used for API authentication (`laravel/sanctum` in composer.json and middleware traces in logs).
- Project layout: standard Laravel layout with important paths:
  - Models: `app/Models` (namespace `App\Models`) — e.g. `app/Models/User.php`.
  - Controllers: `app/Http/Controllers`.
  - Service providers: `app/Providers` (see `RouteServiceProvider.php` for route registration).
  - Routes: `routes/api.php` (API), `routes/web.php` (web)
  - Migrations: `database/migrations`
  - Tests: `tests/Feature`, `tests/Unit`; `phpunit.xml` is present.
  - Logs: `storage/logs/laravel.log` (useful for runtime errors like DI/binding issues).

## Project-specific conventions and patterns
- Autoload: `composer.json` maps `App\` -> `app/` (PSR-4). Do not move namespaces without updating composer autoload and running `composer dump-autoload`.
- User roles: `users` have a string `role` column and helper methods on `App\Models\User` (e.g. `isAdmin()`, `assignRole()`). Changing role handling affects business logic across models and controllers.
- Profiles: `User` may auto-create `Student` or `Instructor` profiles (see `User::assignRole` behavior). Be careful when creating/deleting related profiles.
- Routes: `RouteServiceProvider::boot()` groups `api.php` under `api` prefix; keep API controllers in `app/Http/Controllers` and use route-model binding conventions (see debugging note below).

## Common developer workflows & commands
- Install dependencies: `composer install` then `npm install` (or use the `setup` script below).
- Quick project bootstrap (composer script): `composer run setup` — runs composer install, copies `.env`, `php artisan key:generate`, runs migrations, and builds assets.
- Local dev (composed script): `composer run dev` — runs `php artisan serve`, queue listener, pail, and Vite concurrently.
- Run tests: `composer run test` or `php artisan test` (composer `test` script clears config and runs artisan test).
- Run migrations: `php artisan migrate` (use `--force` in CI). A sqlite file is touched by `post-create-project-cmd` in composer if desired (`database/database.sqlite`).

## Debugging and common pitfalls (concrete examples)
- If you see "Target class [role] does not exist" in `storage/logs/laravel.log`, it's likely caused by route-model binding or an incorrect implicit binding parameter (container trying to resolve `role`).
  - Check `routes/api.php` / `routes/web.php` for route parameters named `{role}` and controller method signatures.
  - Check `app/Providers/RouteServiceProvider.php` for custom bindings or `Route::model` registration.
  - If a `Role` model is expected but missing, either create `App\Models\Role` or rename the route parameter (e.g. `{role}` -> `{roleId}`) and fetch manually.
  - Useful commands: `php artisan route:list` to inspect parameters, and `php artisan tinker` to test bindings interactively.

## Integration points & notable packages
- Sanctum for API auth (see `composer.json` and middleware traces in logs).
- Vite is used for frontend asset bundling (see `vite.config.js` and `npm` scripts).
- Dev tools: `laravel/pint`, `laravel/pail`, and `laravel/sail` are present in composer dev deps.

## Where to look for examples in this repo
- Role/profile logic: `app/Models/User.php`
- Route grouping and where API routes live: `app/Providers/RouteServiceProvider.php`, `routes/api.php`
- Migrations naming and location: `database/migrations/*`
- Tests: `tests/Feature`, `tests/Unit`
- Runtime issues and logs: `storage/logs/laravel.log`

## Editing & PR guidance (small checklist)
1. Keep models in `App\Models` and controllers in `App\Http\Controllers` unless you update `composer.json` autoload.
2. Run `composer dump-autoload` after moving/renaming classes.
3. Run `composer run test` before opening a PR (basic smoke tests).
4. When changing role/profile behavior, search for usages of `$user->role`, `assignRole`, and `studentProfile` / `instructorProfile` to avoid regressions.

---
If any section is unclear or you want more examples (e.g., typical unit test structure or sample API controller), tell me which area to expand and I will update this file.
