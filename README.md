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

3. Create the database — run migrations **in numeric order** (`001` → `020`):

**Windows (PowerShell + XAMPP):**

```powershell
$mysql = 'D:\xamp\mysql\bin\mysql.exe'
$migrations = 1..20 | ForEach-Object { '{0:D3}' -f $_ }
foreach ($n in $migrations) {
    Get-ChildItem "database\migrations\${n}_*.sql" | ForEach-Object {
        Write-Host "Running $($_.Name)..."
        cmd /c "`"$mysql`" -u root --default-character-set=utf8mb4 < `"$($_.FullName)`""
    }
}
```

Or use the helper script (recommended for Thai text):

```powershell
.\scripts\run-sql.ps1 database\migrations\020_seed_demo_users.sql
```

If Thai text in seeded data shows as `?`, re-apply demo names:

```powershell
.\scripts\run-sql.ps1 database\fixes\003_fix_demo_user_names.sql
```

**Linux / macOS:**

```bash
for f in database/migrations/*.sql; do mysql -u root < "$f"; done
```

Key migrations include `015` (module discussions), `016` (gamification), `017` (certificates), `018` (discussion read tracking for instructor unread badges), `019` (student ID / title prefix), and `020` (demo user accounts — see [`docs/DEMO_ACCOUNTS.md`](docs/DEMO_ACCOUNTS.md)).

4. Access the application:

```
http://localhost/fitcoch/public
```

Or open the project root (auto-redirects to `public/`):

```
http://localhost/fitcoch
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

## Sprint 2 — Profiles & Roles

### Web Routes
- `GET /profile` — Profile settings (timezone, name)
- `POST /profile` — Update profile

### API Routes (`/api/v1`)
- `GET /api/v1/users/me` — Current user profile + stats placeholder
- `GET /api/v1/instructor/ping` — RBAC test (instructor/admin only)
- `GET /api/v1/admin/users` — List all users (admin only)

### Admin Web Routes
- `GET /admin/users` — Account list with roles and status
- `GET /admin/users/{id}` — Manage roles and suspend/activate
- `POST /admin/users/{id}/roles` — Update access roles
- `POST /admin/users/{id}/status` — Update account status

## Localization

The web UI is **Thai only** (`config/locale.php` — `supported: ['th']`). Copy lives in `lang/th.php`. The `lang/en.php` file remains for reference but is not exposed in the UI.

## Tests

```bash
composer test
```

## Documentation

See the [`docs/`](docs/) directory for architecture, database schema, API specification, and sprint roadmap.
