# SIMKM — Technical Architecture

Sistem Informasi Manajemen Kesehatan Mental: a campus mental-health platform for
students, counselors, and admins — self-assessments (BDI-II + PWB), structured diary
entries, counseling booking/scheduling, self-help activities, chat, and 8 aggregate
Laporan (report) pages with PDF export.

This document covers the tech stack, how the Docker setup fits together, the folder
layout, and how a request flows through the app's layers. For "how do I get this
running" see [SETUP.md](SETUP.md) (local/XAMPP) or the Docker section below; for "how
does a push reach the VPS" see [DEPLOY.md](DEPLOY.md).

---

## 1. Tech Stack

**Backend** — deliberately framework-free PHP, not Laravel/Symfony. `composer.json`
declares it explicitly: `"description": "Example framework-free PHP application"`.

| Piece | What | Notes |
|---|---|---|
| Language | PHP >= 8.1 (8.3 in Docker) | typed properties, `match`, constructor promotion used throughout |
| Routing | Hand-rolled `App\Core\Router` | ~30 lines; regex path matching, no route caching/groups |
| DB access | `mysqli` + prepared statements | no ORM, no query builder — every repository writes raw SQL |
| PDF export | `dompdf/dompdf` (^3.1) | used for every Laporan PDF (`ReportPdfService`) and assessment result PDFs |
| Env config | `vlucas/phpdotenv` (^5.6) | loads `.env`; `config/app.php` + the `env()`/`config()` helpers wrap it |
| Email | PHPMailer, **vendored under `config/phpmailer/src/`** (not via Composer) | wired through `config/send_email.php`'s `kirimEmail()`, used by password reset, notifications, admin-approval flows. `App\Services\MailService` is a second, separate path using PHP's built-in `mail()` — only used for the welcome email in `UserController`. Two parallel mail mechanisms exist; worth consolidating if you touch this area. |
| Autoloading | Composer PSR-4 | `App\` → `src/`, plus `src/Helpers/functions.php` autoloaded as a plain file |

**Frontend** — no build step, no `package.json`, no bundler. Every page is server-rendered
PHP with vanilla JS and CDN-loaded libraries:
- Bootstrap 5 (CSS + bundled JS) — layout, modals, dropdowns, toasts
- Chart.js — report charts (trend lines, bar distributions)
- Flatpickr — date inputs app-wide (see `templates/layouts/index.php`, displays `dd/mm/yyyy`, submits `yyyy-mm-dd`)
- Bootstrap Icons (a couple of auth pages only)
- A couple of static local files: `public/assets/css/app.css`, `public/assets/js/assessment-session.js`

**Database** — MySQL 8.0. Schema evolves via numbered SQL files in
`database/migrations/`, applied by `bin/migrate.php` (tracked in a `schema_migrations`
table). `database/mental_health_dump.sql` is a full schema+data snapshot used to seed
fresh environments.

**Infrastructure** — Docker (Apache `mod_php` + MySQL 8 via Compose locally; the VPS
runs the same images), GitHub Actions for CI/CD (`.github/workflows/deploy.yml` →
`deploy.sh` on push to `main`).

---

## 2. Docker Setup

Two services, defined in [`docker-compose.yaml`](docker-compose.yaml):

```yaml
services:
  app:                          # container: simkm-app
    build: .                    # from ./Dockerfile
    ports: ['8000:80']
    environment: { DB_HOST: mysql, DB_PORT: 3306, DB_DATABASE: mental_health, DB_USERNAME: appuser, DB_PASSWORD: p@ssw0rd }
    env_file: [.env]
    volumes: ['./public/uploads:/var/www/html/public/uploads']
    depends_on: [mysql]

  mysql:                         # container: mysql-dev
    image: mysql:8.0
    ports: ['3306:3306']
    environment: { MYSQL_ROOT_PASSWORD: root, MYSQL_DATABASE: mental_health, MYSQL_USER: appuser, MYSQL_PASSWORD: p@ssw0rd }
    volumes: ['mysql_data:/var/lib/mysql']   # named volume — survives container recreation
```

Key points:
- `app`'s `environment:` block **overrides** whatever `DB_*` values are in your local
  `.env` (via `env_file`) — inside the container, `DB_HOST` is always the Compose
  service name `mysql`, resolved over the Compose-managed Docker network. This is a
  common trip-up: editing `.env`'s `DB_HOST` has no effect on the containerized app.
- `public/uploads/` is bind-mounted, not baked into the image — uploaded profile
  photos, article images, and org logos persist across rebuilds. Everything else
  under `/var/www/html` comes from the image (`COPY . .` in the Dockerfile), so a
  plain `docker cp` into a running container is only a temporary hot-patch — it's
  lost on the next `docker compose build`/recreate unless the change also lands in
  the repo.
- MySQL data lives in the named volume `mysql_data`, not a bind mount — `docker
  compose down` alone won't delete it, but `docker compose down -v` will.

**[`Dockerfile`](Dockerfile)** (single stage, `php:8.3-apache` base):
1. `apt-get install` build deps + `docker-php-ext-install mysqli gd` — `gd` is
   required by dompdf to embed raster images (e.g. the org logo) into PDFs; without
   it, any PDF export touching an image fatals with "The PHP GD extension is required".
2. Points Apache's `DocumentRoot` at `/var/www/html/public` (not the repo root) and
   enables `.htaccess` overrides — this is what makes `public/` the actual web root
   while the rest of the app (`src/`, `templates/`, `config/`) stays outside it,
   unreachable directly over HTTP.
3. Copies in Composer (from the official `composer:2` image, not installed via apt),
   then `COPY . .` + `composer install --no-dev --optimize-autoloader`.
4. `docker-entrypoint.sh` rewrites Apache's listen port from the `PORT` env var (for
   platforms that inject a dynamic port) and execs `apache2-foreground`.

**Common commands:**
```bash
docker compose up -d              # start both containers
docker compose build app          # rebuild just the app image (e.g. after a Dockerfile change)
docker compose up -d --no-deps app  # recreate only app, leave mysql (and its data) untouched
docker compose exec app php bin/migrate.php   # run pending migrations inside the container
docker compose logs -f app        # tail app logs
```

---

## 3. Project Structure

```
public/                 ← Apache's actual web root (only this is reachable over HTTP)
  index.php             ← single entry point: bootstraps env, builds the Router, dispatches
  assets/                 static css/js (no build step)
  uploads/                 user-uploaded files (profile photos, article images, org logos) — bind-mounted in Docker

src/                    ← PSR-4 "App\" — everything else, not web-reachable
  Core/                    framework primitives: Router, Request, Response, Database
  Middleware/              AuthMiddleware (that's currently the only one)
  Controllers/             one class per feature area (28 files) — HTTP-facing
  Repositories/            one class per domain table/aggregate (20 files) — all SQL lives here
  Models/                  plain hydration classes (18 files) — typed properties, no behavior
  Services/                cross-cutting logic that doesn't fit a single repository (scoring, PDF, mail, notifications)
  Support/                 static reference data (e.g. assessment instrument metadata)
  Helpers/functions.php    global helper functions (env(), config(), mood_meta(), etc.), autoloaded as a file

templates/              ← plain PHP view files (not Blade/Twig), one subfolder per feature
  layouts/                 shared page shells: index.php (logged-in app shell), public.php, main.php
  partials/                cross-cutting includes reused by unrelated features (e.g. password_toggle.php)
  <feature>/               _styles.php (scoped CSS returned as a string) + _*.php partials + page templates
  errors/                  404.php etc.

config/                 ← plain PHP files returning arrays, or defining helper functions
  routes.php               every route, registered against $router (122 routes)
  app.php                  app name/env/debug/url, read via config('app.xxx')
  send_email.php            kirimEmail() — SMTP sending via the vendored PHPMailer
  phpmailer/                vendored PHPMailer source (not a Composer package)

database/
  migrations/              numbered *.sql files, applied by bin/migrate.php
  seeders/                  one-off PHP scripts for demo/test data (run manually, not on every deploy)
  mental_health_dump.sql   full schema+data snapshot, used to seed a fresh environment
  erd.dot, erd.png          combined ERD; database/erd/ holds the same broken out per module (10 diagrams)

bin/migrate.php          ← CLI migration runner (see DEPLOY.md for --baseline usage)
test-evidence/           ← numbered screenshot folders per feature, evidence for the thesis writeup — not part of the running app

Dockerfile, docker-compose.yaml, docker-entrypoint.sh   ← see §2
.github/workflows/deploy.yml, deploy.sh                  ← CI/CD, see DEPLOY.md
SETUP.md, DEPLOY.md, ARCHITECTURE.md (this file)
```

---

## 4. Architecture & Request Flow (the Layers)

There's no framework enforcing this — it's a convention followed consistently across
all 28 controllers. Tracing one real request end to end:

```
Browser
  │  GET /laporan/diary
  ▼
public/index.php            bootstraps: autoload, session_start(), .env, error display
  │
  ▼
config/routes.php           $router->get('/laporan/diary', [ReportController::class, 'diary']);
  │
  ▼
App\Core\Router::dispatch() matches the path against every registered regex pattern,
  │                          instantiates the controller + a fresh Request, calls the action
  ▼
Controller (App\Controllers\ReportController)
  │   - constructor runs AuthMiddleware::handle() (redirects to /login if no session)
  │   - constructs whichever Repositories/Services it needs
  │   - the action method reads $request->get()/post(), applies role-based scoping
  │     (student sees their own data; counselor sees their assigned students;
  │     admin sees everything — see ReportController::applyScope())
  ▼
Repository (App\Repositories\ReportRepository)
  │   - builds parameterized SQL, executes via App\Core\Database::connection() (mysqli singleton)
  │   - returns raw associative arrays OR hydrates them into Models before returning
  ▼
Model (App\Models\DiaryEntry, etc.)
  │   - plain constructor-hydration + toArray(); casts/decodes JSON columns
  │   - no querying, no business logic — a typed shape, nothing more
  ▼
back in the Controller
  │   - merges/shapes the data, calls Response::view('laporan/diary', [...])
  ▼
App\Core\Response::view()   extract()s the data array, require()s the matching
  │                          templates/*.php file
  ▼
templates/laporan/diary.php require()s templates/layouts/index.php, which wraps
  │                          the page content in the shared topbar/sidebar/footer
  ▼
HTML response → Browser
```

**Layer responsibilities, summarized:**

| Layer | Lives in | Responsibility | Must NOT do |
|---|---|---|---|
| Core | `src/Core/` | HTTP plumbing: routing, request/response, the single DB connection | Know about any specific feature |
| Middleware | `src/Middleware/` | Cross-cutting request gates (currently just session auth) | Contain feature logic |
| Controllers | `src/Controllers/` | Read the request, enforce role/permission checks, orchestrate repositories/services, pick a view or JSON/redirect response | Write raw SQL directly, contain HTML |
| Repositories | `src/Repositories/` | All SQL for one domain/table; return raw arrays or hydrated Models | Make authorization decisions, know about HTTP |
| Models | `src/Models/` | Typed hydration of a DB row into a PHP object (`toArray()` back to array) | Query the database, contain business rules |
| Services | `src/Services/` | Logic that spans repositories or isn't data-access at all (score calculation, PDF rendering, email sending, notification fan-out) | Read `$_GET`/`$_POST`/`$_SESSION` directly |
| Templates | `templates/` | Render HTML from the data a controller handed it | Query the database — *with one deliberate exception*: `templates/layouts/index.php` instantiates `NotificationRepository`/`UserRepository`/`CounselorRepository` directly for cross-cutting chrome (notification bell, sidebar user info) so every controller doesn't have to remember to pass it in |

**Patterns worth knowing before you extend a report or add a feature:**
- **One controller per feature area**, not per-model — e.g. `ReportController` owns all
  8 Laporan pages; `AdminCounselorController` owns counselor CRUD.
  Each report action follows the same shape: a private `xxxData()` method fetches +
  shapes data (shared by both the HTML view and the PDF export action), a public
  `xxx()` renders the view, a public `xxxPdf()` streams the PDF — see
  `ReportController::diaryData()`/`diary()`/`diaryPdf()`.
- **Role scoping is centralized**: `ReportController::applyScope()` is the single
  place that decides what a student/counselor/admin is allowed to see for a given
  report slug — don't duplicate that logic in individual actions.
- **Settings** (assessment time limit, default report counselor, org letterhead) live
  in a generic `app_settings` key-value table via `SettingsRepository::get()/set()` —
  not dedicated columns — see `AdminSettingsController`.
- **No global exception→HTTP-error handler**: uncaught exceptions surface as raw PHP
  fatals (visible if `APP_DEBUG=true`, blank/500 otherwise). Repositories rely on
  `mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)` (set once in
  `Database::connection()`) so bad SQL throws instead of failing silently.
