# FitCoch — Flipped-Microlearning MOOC Platform

PHP 8.3 custom MVC application for microlearning and flipped classroom delivery.

## Requirements

- PHP 8.3+
- MySQL 8
- Apache with `mod_rewrite` (XAMPP)
- Composer

## Setup (XAMPP)

1. Install dependencies:

```bash
composer install
```

2. Copy environment file and configure database credentials:

```bash
copy .env.example .env
```

3. Create the database and `users` table:

**Windows (PowerShell + XAMPP):**

```powershell
Get-Content database\migrations\001_create_users_table.sql -Raw | D:\xamp\mysql\bin\mysql.exe -u root
```

**Linux / macOS:**

```bash
mysql -u root < database/migrations/001_create_users_table.sql
```

4. Point Apache to the `public/` directory, or access via:

```
http://localhost/fitcoch/public
```

## Sprint 1 — Authentication

### Web Routes
- `GET /login` — Sign in page
- `POST /login` — Authenticate (session cookie)
- `GET /register` — Registration page
- `POST /register` — Create account
- `POST /logout` — End session
- `GET /dashboard` — Protected home (requires auth)

### API Routes (`/api/v1`)
- `POST /api/v1/auth/login` — Returns JWT + user profile
- `POST /api/v1/auth/register` — Creates user (201)
- `POST /api/v1/auth/logout` — Invalidates session/token

## Tests

```bash
composer test
```

## Documentation

See the [`docs/`](docs/) directory for architecture, database schema, API specification, and sprint roadmap.
