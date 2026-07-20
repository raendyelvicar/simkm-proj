# Automated Deploy to VPS

Push to `main` → GitHub Actions SSHes into the VPS → pulls latest code → rebuilds
the `app` container → applies any pending DB migrations.

Pieces:
- [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml) — the CI trigger.
- [`deploy.sh`](deploy.sh) — the actual deploy steps, run on the VPS.
- [`bin/migrate.php`](bin/migrate.php) — applies `database/migrations/*.sql` files
  that haven't run yet, tracked in a `schema_migrations` table.

## One-time VPS setup

1. Make sure the app is deployed on the VPS as a plain git clone (not just an
   unpacked archive) — `deploy.sh` runs `git fetch && git reset --hard origin/main`
   inside it. If it isn't a clone yet:
   ```bash
   cd /path/to/where/you/want/it
   git clone https://github.com/raendyelvicar/simkm-proj.git
   cd simkm-proj
   cp .env.copy .env   # then fill in real DB credentials
   ```
   If the repo is private, use an SSH remote instead and give the deploy key
   (below) read access, or a separate read-only deploy key for git itself.

2. Generate a dedicated SSH keypair **for GitHub Actions to use** (don't reuse
   your personal key):
   ```bash
   ssh-keygen -t ed25519 -f ./gh-deploy-key -N "" -C "github-actions-deploy"
   ```
   Append `gh-deploy-key.pub` to `~/.ssh/authorized_keys` **on the VPS**, for
   the user that owns the app directory and can run `docker`/`git` there.

3. In the GitHub repo → **Settings → Secrets and variables → Actions**, add:
   | Secret | Value |
   |---|---|
   | `VPS_HOST` | VPS IP or hostname |
   | `VPS_USER` | SSH username on the VPS |
   | `VPS_SSH_KEY` | contents of `gh-deploy-key` (the **private** key) |
   | `VPS_PORT` | SSH port, only if not 22 |
   | `VPS_APP_DIR` | absolute path to the git clone, e.g. `/home/deploy/simkm-proj` |

   Delete the local `gh-deploy-key`/`gh-deploy-key.pub` files once the secret is saved.

4. **Baseline the migration tracker once**, before the first automated deploy —
   important since this VPS's database was already provisioned (from
   `database/mental_health_dump.sql` or by hand), not by running these `.sql`
   files fresh. Without this, the first deploy will fail with errors like
   "table already exists":
   ```bash
   cd /path/to/simkm-proj
   docker compose exec app php bin/migrate.php --baseline
   ```
   This records every migration file currently in the repo as already-applied
   *without running them*. Confirm first that the database really does already
   have everything up to that point (it should, if it came from the dump).
   Any migration file added to the repo after this point applies normally on
   the next deploy.

## Using it

- **Automatic**: push to `main`, or merge a PR into it.
- **Manual re-run**: GitHub repo → Actions → "Deploy to VPS" → Run workflow.
- **Manual, from the VPS itself** (e.g. to debug): `cd $VPS_APP_DIR && bash deploy.sh`.

## Notes

- `deploy.sh` does `git reset --hard origin/main` — **don't hand-edit files in
  the VPS checkout**, they'll be discarded on the next deploy. `.env` and
  `public/uploads/` are gitignored, so they're untouched.
- The `mysql` service only restarts if its own config changes — a normal code
  deploy rebuilds/restarts just the `app` container, so the DB stays up.
- `bin/migrate.php` is safe to run repeatedly; it only executes files not yet
  in `schema_migrations`.
- To roll back: revert the bad commit and push (redeploys the reverted code),
  or SSH in, `git checkout <good-sha>`, and run `bash deploy.sh` by hand. Note
  this only rolls back code — it does not undo migrations that already ran.
