<?php
// API root — a simple health check / endpoint listing.

$config = require __DIR__ . '/../app/bootstrap.php';
cors($config['allowed_origins']);

json_out([
    'name'      => 'Four Wheels Zone API',
    'status'    => 'ok',
    'endpoints' => [
        'GET  /reviews.php'            => 'All approved reviews + aggregate + distribution',
        'GET  /reviews.php?rating=5'   => 'Only 5-star reviews (aggregate stays global)',
        'GET  /reviews.php?limit=6'    => 'Latest 6 (for the homepage)',
        'POST /reviews.php'            => 'Create a review { name, vehicle?, rating, comment }',
    ],
]);
