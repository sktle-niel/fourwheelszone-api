# Four Wheels Zone — API backend

Standalone PHP + MySQL API for the Four Wheels Zone website. Currently powers
the **Reviews** feature (read approved reviews + aggregate, submit a new review).

It is meant to run on its own origin — e.g. `https://api.fourwheelszone.com` —
separate from the React frontend. Plain PHP, no Composer, no framework, so it
drops straight onto Hostinger shared hosting.

## Structure

```
fourwheelszone-api-backend/
├── public/              <- web root (point the subdomain document root here)
│   ├── index.php        <- health check / endpoint list
│   ├── reviews.php      <- the reviews endpoint
│   └── .htaccess
├── app/                 <- application code (NOT web-accessible)
│   ├── bootstrap.php    <- loads .env + helpers, returns config
│   ├── config.php       <- reads env vars
│   ├── db.php           <- PDO MySQL singleton
│   ├── env.php          <- tiny .env loader
│   └── http.php         <- cors(), json_out(), read_json_body()
├── database/
│   └── schema.sql       <- import this in phpMyAdmin (table + seed)
├── .env.example         <- copy to .env and fill in
└── .gitignore
```

Secrets and source live **outside** `public/`, so only `public/` is ever
exposed to the web.

## Endpoints

| Method | Path                  | Description                                            |
|--------|-----------------------|--------------------------------------------------------|
| GET    | `/reviews.php`        | All approved reviews + `count`, `average`, `distribution` |
| GET    | `/reviews.php?rating=5` | Only 5-star reviews (aggregate stays global)         |
| GET    | `/reviews.php?limit=6`  | Latest 6 (used by the homepage)                      |
| POST   | `/reviews.php`        | Create a review `{ name, vehicle?, rating, comment }`  |

## Local development

```bash
cp .env.example .env        # then edit DB_* for your local MySQL
php -S localhost:8000 -t public
```

API is now at `http://localhost:8000/reviews.php`.

In the **frontend** repo, point Vite at it:

```
# .env.local
VITE_API_BASE=http://localhost:8000
```

## Deploy

See [DEPLOY.md](DEPLOY.md) for the full Hostinger subdomain walkthrough.
