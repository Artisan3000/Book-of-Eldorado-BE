# API Testing Guide

This document shows example API requests, expected responses, and how to run integration tests for the LMS API.

## Quick reference

- Base URL (local dev): `http://127.0.0.1:8000/api`
- Auth: Laravel Sanctum Bearer token. Include header `Authorization: Bearer <token>`.

## Useful endpoints (examples)

1. Admin: Student overview

- GET `/api/admin/student/{id}/overview`
- Requires `admin` role.
- Response shape example:

```
{
  "student": {
    "id": 12,
    "name": "Maria",
    "email": "maria@student.artisanbarber.com",
    "streak": 6,
    "total_hours": 42,
    "courses_in_progress": 2,
    "certificates_earned": 3
  },
  "recent_courses": [
    { "id": 1, "title": "Foundation", "slug": "foundation", "progress": 0.75 },
    { "id": 2, "title": "Refinement", "slug": "refinement", "progress": 0.4 }
  ],
  "recommendations": [
    { "id": 8, "title": "Luxury Service 101", "price": 60, "reason": "Similar to Refinement" }
  ],
  "achievements": [
    { "id": 1, "title": "Foundation Certificate", "date": "2025-08-12" }
  ]
}
```

2. Student: My courses

- GET `/api/student/courses` (authenticated student)

3. Student: Course details

- GET `/api/student/courses/{slug}` (authenticated student)

## Running the test suite

Run PHPUnit tests:

```powershell
./vendor/bin/phpunit
# or
composer run test
```

If tests require a fresh database, configure `phpunit.xml` to use an in-memory sqlite database or set up a test database in `.env.testing`.

## Creating test data (seeders)

Add seeder classes under `database/seeders` and call them from `DatabaseSeeder.php`.

Example to create a demo seeder:

```powershell
php artisan make:seeder DemoDataSeeder
# edit DemoDataSeeder.php to insert test rows
php artisan db:seed --class=DemoDataSeeder
```

## Manual API testing (curl / PowerShell)

1. Register a user and create an API token (or create a token via tinker).

2. Call endpoints with `Authorization: Bearer <token>` header.

Example PowerShell:

```powershell
$token = "<ADMIN_TOKEN>"
Invoke-RestMethod -Method Get -Uri http://127.0.0.1:8000/api/admin/student/12/overview -Headers @{ Authorization = "Bearer $token" }
```

## Postman / Insomnia

- Import a Postman collection (if available).
- Set `baseUrl` environment variable to `http://127.0.0.1:8000/api`.
- Add `adminToken` / `userToken` env variables for auth testing.

---

If you want, I can also:
- Add example Postman collection JSON to `docs/`.
- Create `database/seeders/DemoDataSeeder.php` with realistic demo rows for one course and a student.
- Add a few feature tests that exercise the main endpoints (overview, my courses, course details).
