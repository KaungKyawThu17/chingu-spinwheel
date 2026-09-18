# Deploy to Render

The root `Dockerfile` builds Vite assets with Node 22 and runs Laravel with PHP
8.3 and Apache. It installs the locked production Composer dependencies and the
MySQL, Intl, and ZIP extensions. Local `.env` files, logs, cached configuration,
database files, and installed dependencies are excluded from the image.

## Web service settings

Push these files to your repository, then create a Render **Web Service**:

| Setting | Value |
| --- | --- |
| Source | Your Git repository |
| Branch | The branch containing the Dockerfile |
| Language / runtime | Docker |
| Root directory | Leave blank |
| Dockerfile path | `./Dockerfile` |
| Docker build context | `.` |
| Docker command | Leave blank; use the image's default command |
| Instance type | Free for a demo |

Apache serves only `public/` and listens on Render's `PORT` (default `10000`).

## Environment variables

Set these in Render's Environment settings, not in the Dockerfile:

| Variable | Value |
| --- | --- |
| `APP_NAME` | `Chingu Spinwheel` |
| `APP_KEY` | Generate once with `php artisan key:generate --show` and keep it stable |
| `APP_URL` | Your full `https://…onrender.com` URL, or HTTPS custom domain |
| `TRUSTED_PROXIES` | `*` on Render, so Laravel respects forwarded HTTPS and client IP headers |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Your external MySQL hostname |
| `DB_PORT` | Your database provider's port |
| `DB_DATABASE` | Your database name |
| `DB_USERNAME` | Your database username |
| `DB_PASSWORD` | Your database password |
| `SESSION_DRIVER` | `cookie` to keep sessions through container restarts |
| `SESSION_SECURE_COOKIE` | `true` |
| `CACHE_DRIVER` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `RUN_MIGRATIONS` | `true` to apply pending migrations at startup |

The image defaults to `APP_ENV=production`, `APP_DEBUG=false`, and logging to
stderr so logs appear in Render. Startup requires `APP_KEY`, caches Laravel
configuration and views, and starts Apache. It never generates a new key or seeds
the database automatically. Migrations are disabled unless `RUN_MIGRATIONS=true`.

Use an external MySQL database, such as Aiven. An existing migration uses
MySQL-specific SQL, so PostgreSQL is not a direct replacement. If your provider
requires a CA certificate, upload it as a Render secret file (for example,
`mysql-ca.pem`) and set `MYSQL_ATTR_SSL_CA=/etc/secrets/mysql-ca.pem`.

For a new database, seed the initial prizes/questions and create an administrator
separately. Run these commands from a trusted machine configured for that database:

```sh
php artisan db:seed --force
php artisan make:filament-user
php artisan shield:super-admin --user=USER_ID --panel=admin
```

Replace `USER_ID` with the administrator's database ID. Create an active event in
the admin panel before accepting survey submissions. Do not reseed on every
deployment: the seeders update prize values and recreate survey question options.

## Local Docker check

```sh
docker build -t chingu-spinwheel .
docker run --rm -p 10000:10000 --env-file .env.docker chingu-spinwheel
```

Create the ignored `.env.docker` file with the variables above. For local HTTP,
use `APP_URL=http://localhost:10000` and `SESSION_SECURE_COOKIE=false`.
For direct local access, leave `TRUSTED_PROXIES` unset. The wildcard is intended
for services reachable through a trusted reverse proxy.
Database host `127.0.0.1` refers to the container itself; use an external database hostname
or `host.docker.internal` for a database on your Mac. Do not enable migrations
against an existing database unless you intend to apply them.

## Free-tier limits

Render Free sleeps after 15 minutes without traffic and discards local files on
restart, redeploy, or sleep. Survey records remain in external MySQL, but local
CSV/XLSX exports and the file cache are temporary. Store exports externally or
use a persistent disk on a paid instance when retention is needed.

This container runs the web server only. The existing event activation command
is scheduled daily at 00:05; run `php artisan schedule:run` every minute from a
separate scheduler with the same application/database configuration. A sleeping
free web service cannot guarantee scheduled execution. No queue worker is needed
with `QUEUE_CONNECTION=sync`.

References: [Render Docker deployments](https://render.com/docs/docker),
[Laravel on Render](https://render.com/docs/deploy-php-laravel-docker), and
[Render Free limits](https://render.com/docs/free).
