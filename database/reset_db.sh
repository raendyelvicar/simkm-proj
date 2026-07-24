#!/usr/bin/env bash
#
# Fully resets the app's database: drops every existing table, reprovisions a
# fresh schema + seed data from mental_health_dump.sql, then marks every
# migration file on disk as already applied (since the dump already contains
# them all) so the next `php bin/migrate.php` on deploy doesn't try to re-run
# CREATE TABLE statements against tables that already exist.
#
# Reads DB connection info from .env in the project root (same vars the app
# itself uses — see src/Core/Database.php).
#
# Usage: ./database/reset_db.sh
#
# WARNING: this deletes all data in the target database. There is no undo.

set -euo pipefail
cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
    echo "Missing .env in project root — copy .env.example to .env and configure DB_* first." >&2
    exit 1
fi

set -a
source .env
set +a

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_DATABASE="${DB_DATABASE:-mental_health}"

MYSQL_ARGS=(-h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME")
if [ -n "$DB_PASSWORD" ]; then
    MYSQL_ARGS+=(-p"$DB_PASSWORD")
fi

read -r -p "This will DROP ALL TABLES in '${DB_DATABASE}' on ${DB_HOST}:${DB_PORT} and reload from mental_health_dump.sql. Continue? [y/N] " confirm
if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

echo "==> Dropping all tables in '${DB_DATABASE}'..."
mysql "${MYSQL_ARGS[@]}" "$DB_DATABASE" < database/drop_all_tables.sql

echo "==> Importing fresh schema + seed data..."
mysql "${MYSQL_ARGS[@]}" < database/mental_health_dump.sql

echo "==> Marking existing migration files as already applied..."
php bin/migrate.php --baseline

echo "==> Done. Database '${DB_DATABASE}' has been reset."
