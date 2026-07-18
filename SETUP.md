# Setup Guide — SIMKM (Windows / XAMPP)

Framework-free PHP application. Requires PHP >= 8.1 (XAMPP's bundled PHP works),
Composer, and MySQL. Commands below are for Windows Command Prompt / PowerShell.

## 1. Clone the repo into XAMPP's htdocs

```bat
cd C:\xampp\htdocs
git clone <repo-url> simkm-proj
cd simkm-proj
```

## 2. Install PHP dependencies

Make sure `C:\xampp\php` is on your `PATH` (so `php` and `composer` resolve to the
XAMPP-bundled PHP), then:

```bat
composer install
```

## 3. Configure environment

```bat
copy .env.copy .env
```

Edit `.env` and set your local DB credentials (XAMPP's default MySQL user is `root`
with no password):

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mental_health
DB_USERNAME=root
DB_PASSWORD=
```

Leave `APP_URL=http://localhost:8000` unless you're serving from a different host/port
(see the vhost option below).

## 4. Create the database and import data

Start MySQL from the XAMPP Control Panel, then either:

- **phpMyAdmin** (easiest on Windows): open `http://localhost/phpmyadmin`, create a
  database named `mental_health`, select it, go to **Import**, and choose
  `database\mental_health_dump.sql`.
- **Command line**:
  ```bat
  "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE mental_health"
  "C:\xampp\mysql\bin\mysql.exe" -u root mental_health < database\mental_health_dump.sql
  ```

The dump is a full schema + data snapshot. `database\migrations\` holds the individual
migrations it was built from — only needed if you want to apply schema changes
incrementally instead of using the dump.

## 5. Run the application

Two ways to serve it on Windows:

### Option A — PHP built-in server (simplest, no Apache config)

```bat
cd C:\xampp\htdocs\simkm-proj
php -S localhost:8000 -t public
```

Visit **http://localhost:8000/** — matches `APP_URL` already in `.env`.

### Option B — XAMPP Apache with a virtual host

The router does exact path matching with no base-path support, so the app must be
served from the domain root — **not** a subfolder like `http://localhost/simkm-proj/`
(that hits the app's own 404 page).

1. Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`, add:
   ```apache
   <VirtualHost *:80>
       ServerName simkm-proj.local
       DocumentRoot "C:/xampp/htdocs/simkm-proj/public"
       <Directory "C:/xampp/htdocs/simkm-proj/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. Edit `C:\Windows\System32\drivers\etc\hosts` (as Administrator), add:
   ```
   127.0.0.1 simkm-proj.local
   ```
3. Update `.env`: `APP_URL=http://simkm-proj.local`
4. Restart Apache from the XAMPP Control Panel.
5. Visit **http://simkm-proj.local/**.
