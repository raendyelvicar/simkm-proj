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

echo "==> Pruning dangling images"
docker image prune -f >/dev/null 2>&1 || true

echo "==> Deploy complete"
