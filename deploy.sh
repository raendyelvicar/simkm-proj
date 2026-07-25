#!/usr/bin/env bash
# Runs ON THE VPS, inside the app's git checkout. Pulls the latest main,
# rebuilds/restarts the app container, and applies any pending DB migrations.
#
# The checkout this runs in is treated as deploy-only: local edits made
# directly on the server will be discarded on the next deploy (see the
# `git reset --hard` below). .env and public/uploads are gitignored, so they
# survive untouched.
#
# Usage: run from the app directory, either by hand over SSH or via the
# GitHub Actions workflow at .github/workflows/deploy.yml.
set -euo pipefail

echo "==> Fetching latest code"
git fetch origin
git reset --hard origin/main

if command -v docker-compose >/dev/null 2>&1; then
    COMPOSE="docker-compose"
else
    COMPOSE="docker compose"
fi

echo "==> Building and starting containers"
$COMPOSE -f docker-compose.yaml up -d --build

echo "==> Waiting for the app container"
sleep 3

echo "==> Running database migrations"
$COMPOSE -f docker-compose.yaml exec -T app php bin/migrate.php

# Dummy/demo data (articles, daily tips, self-help activities) — opt-in only,
# since seed_articles.php replaces every existing article and this shouldn't
# run on every automatic push-to-main deploy. Trigger it explicitly with:
#   RUN_SEED=1 bash deploy.sh
if [ "${RUN_SEED:-0}" = "1" ]; then
    echo "==> Seeding dummy data (RUN_SEED=1)"
    $COMPOSE -f docker-compose.yaml exec -T app php database/seed_articles.php
    $COMPOSE -f docker-compose.yaml exec -T app php database/seed_daily_tips.php
    $COMPOSE -f docker-compose.yaml exec -T app php database/seeders/seed_self_help_activities.php
else
    echo "==> Skipping dummy data seed (set RUN_SEED=1 to run it)"
fi

echo "==> Pruning dangling images"
docker image prune -f >/dev/null 2>&1 || true

echo "==> Deploy complete"
