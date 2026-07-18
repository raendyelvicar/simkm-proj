# Setup Guide — SIMKM

Framework-free PHP application. Requires PHP >= 8.1, Composer, and MySQL.

## 1. Clone the repo

```bash
git clone <repo-url> simkm-proj
cd simkm-proj
```

## 2. Install PHP dependencies

```bash
composer install
```

## 3. Configure environment

```bash
cp .env.copy .env
```

Edit `.env` and set your local DB credentials (XAMPP default is usually `root` with no password):

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mental_health
DB_USERNAME=root
DB_PASSWORD=
```

Leave `APP_URL=http://localhost:8000` unless you're serving from a different host/port.

## 4. Create the database and import data

```bash
mysql -u root -e "CREATE DATABASE mental_health"
mysql -u root mental_health < database/mental_health_dump.sql
```

The dump in `database/mental_health_dump.sql` is a full schema + data snapshot. The
`database/migrations/` folder contains the individual migrations it was built from —
only needed if you want to apply schema changes incrementally instead of using the dump.

## 5. Run the application

```bash
php -S localhost:8000 -t public
```

Visit **http://localhost:8000/**.

### Alternative: running under XAMPP/Apache

This app expects to be served from the domain root (matching `APP_URL`), not a
subfolder — the router does exact path matching and has no base-path support. Two
options if you want Apache instead of the PHP built-in server:

- **Virtual host (recommended)**: point a vhost's `DocumentRoot` at `public/`, e.g.
  `ServerName simkm-proj.local` → `DocumentRoot .../simkm-proj/public`, add
  `127.0.0.1 simkm-proj.local` to `/etc/hosts`, and update `APP_URL` accordingly.
- **Subfolder** (`http://localhost/simkm-proj/`): not supported out of the box — you'd
  hit the app's own 404 page because the router doesn't strip the `/simkm-proj` prefix.
