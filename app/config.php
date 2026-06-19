<?php
// Configuration, sourced from environment variables (see .env / .env.example).
// Defaults match a local Laragon / XAMPP install (user "root", empty password).

$origins = getenv('ALLOWED_ORIGINS')
    ?: 'http://localhost:5173,http://localhost:4173,http://127.0.0.1:5173,http://127.0.0.1:4173';

return [
    'host'    => getenv('DB_HOST') ?: 'localhost',
    'port'    => getenv('DB_PORT') ?: '3306',
    'db'      => getenv('DB_NAME') ?: 'fourwheelszone',
    'user'    => getenv('DB_USER') ?: 'root',
    'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    'charset' => 'utf8mb4',

    // Browser origins (your frontend site) allowed to call this API.
    // Comma-separated in ALLOWED_ORIGINS. Use "*" to allow any origin (dev only).
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', $origins)))),
];
