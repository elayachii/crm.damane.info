# AgentFlow CRM Deployment Guide

This document describes the required production deployment steps for AgentFlow CRM on the VPS.

## Production Document Root

The web server document root must point to Laravel's public directory:

```text
/www/wwwroot/crm.rebrandapps.us/backend/public
```

Do not point the web server to the project root or `backend/` directory. Only the `public` directory should be exposed by Nginx/Apache.

## Production Environment File

The `.env` file is ignored by Git and must be created manually on the production server.

Create the production `.env` file inside:

```text
/www/wwwroot/crm.rebrandapps.us/backend/.env
```

Required production values:

```dotenv
APP_NAME="AgentFlow CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://crm.rebrandapps.us

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=agentflow
DB_USERNAME=damaninfo
# DB_PASSWORD must be configured manually on the production server.
DB_PASSWORD=
```

The production database password must be configured directly on the VPS. Never commit real production secrets to Git.

## VPS Deployment Steps

1. Clone or pull the repository into:

```text
/www/wwwroot/crm.rebrandapps.us/backend
```

2. Configure the web server document root:

```text
/www/wwwroot/crm.rebrandapps.us/backend/public
```

3. Create the production `.env` file manually.

4. Install PHP dependencies on the server:

```bash
composer install --no-dev --optimize-autoloader
```

5. Generate the application key if it is not already configured:

```bash
php artisan key:generate
```

6. Run database migrations:

```bash
php artisan migrate --force
```

7. Create the storage symlink:

```bash
php artisan storage:link
```

8. Clear old framework caches:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

9. Rebuild optimized production caches:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

10. Verify the application:

```bash
php artisan about
```

Confirm that the environment is `production`, debug mode is disabled, and the database connection is `mysql`.

## File Permissions

The web server user must be able to write to:

```text
storage/
bootstrap/cache/
```

Recommended ownership and permissions depend on the VPS control panel and web server user. On aaPanel, verify the site user can write to both directories before enabling production traffic.

## Security Checklist

- `.env` exists only on the server and is not committed.
- `APP_DEBUG=false`.
- Web root points to `backend/public`.
- MySQL credentials are configured manually.
- Production database password is not stored in Git.
- HTTPS is enabled for `https://crm.rebrandapps.us`.
- Laravel caches are regenerated after deployment.
