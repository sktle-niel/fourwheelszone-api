# Deploying to Hostinger

Goal: frontend on your main domain (`fourwheelszone.com`) and this API on a
subdomain of the **same Hostinger account** (`api.fourwheelszone.com`). Both
share one MySQL server, so the DB host stays `localhost`.

---

## 1. Create the MySQL database

hPanel → **Databases → MySQL Databases**:

1. Create a database (e.g. `fourwheelszone`). Hostinger prefixes it, so the real
   name becomes something like `u123456_fourwheelszone`.
2. Create a database user and set a password.
3. Assign the user to the database (**All Privileges**).
4. Note down: DB name, DB user, password. Host is `localhost`.

## 2. Import the schema

hPanel → **phpMyAdmin** → select your database → **Import** tab →
choose `database/schema.sql` → **Go**.

> `schema.sql` has no `CREATE DATABASE`/`USE` on purpose — it imports straight
> into the database you just created. You should see a `reviews` table with 6
> seed rows.

## 3. Create the API subdomain

hPanel → **Domains → Subdomains**:

1. Create `api` → `api.fourwheelszone.com`.
2. Set its **document root** to a dedicated folder, e.g. `domains/api.fourwheelszone.com`.
   (You will point it at the `public/` folder in the next step.)

## 4. Upload the files

Upload the **whole project folder** (`app/`, `public/`, `database/`, `.env`)
above the web root, then point the subdomain's document root at `public/`.

Two common ways:

- **File Manager / FTP:** upload `fourwheelszone-api-backend/` into your account
  (e.g. `domains/api.fourwheelszone.com/`), then in **Subdomains** set the
  document root to `domains/api.fourwheelszone.com/public`.
- Whatever you do, the rule is: **only `public/` is the web root**; `app/`,
  `database/`, and `.env` must sit one level above it (not reachable by URL).

## 5. Create the production `.env`

Next to `app/` and `public/` (i.e. the project root, NOT inside `public/`),
create `.env`:

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u123456_fourwheelszone
DB_USER=u123456_fwz
DB_PASS=your-real-db-password

ALLOWED_ORIGINS=https://fourwheelszone.com,https://www.fourwheelszone.com
```

`ALLOWED_ORIGINS` must list the **frontend** site origins (no trailing slash).

## 6. Test the API

Visit:

- `https://api.fourwheelszone.com/` → JSON health check.
- `https://api.fourwheelszone.com/reviews.php` → JSON with `reviews`, `count`,
  `average`, `distribution`.

If you get a 500 with "Database error", re-check the `.env` DB values and that
the import succeeded.

## 7. Point the frontend at the API

In the **frontend** repo, set the API base before building:

```
# .env.production
VITE_API_BASE=https://api.fourwheelszone.com
```

Then `npm run build`, and upload the contents of `dist/` to the main domain's
`public_html/`. The reviews section will now read/write live data from the API.

> If the API is ever unreachable, the site still shows seed reviews (built-in
> fallback), so the page never looks empty.

---

## PHP version

Uses PDO, prepared statements, and `mb_*` functions — works on PHP 7.4+ and
PHP 8.x. Hostinger defaults to PHP 8.x, which is fine. You can confirm/change it
in hPanel → **Advanced → PHP Configuration**.
