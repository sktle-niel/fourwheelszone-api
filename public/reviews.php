<?php
// Reviews API
//   GET  /reviews.php            -> all approved reviews + aggregate + distribution
//   GET  /reviews.php?rating=5   -> only 5-star reviews (aggregate stays global)
//   GET  /reviews.php?limit=6    -> latest 6 (for the homepage)
//   POST /reviews.php            -> create a review  { name, vehicle?, rating, comment }

$config = require __DIR__ . '/../app/bootstrap.php';
cors($config['allowed_origins']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function aggregate(PDO $pdo): array
{
    $agg = $pdo->query("SELECT COUNT(*) c, COALESCE(AVG(rating),0) a FROM reviews WHERE status='approved'")->fetch();
    $dist = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
    foreach ($pdo->query("SELECT rating, COUNT(*) c FROM reviews WHERE status='approved' GROUP BY rating") as $row) {
        $dist[(string)(int)$row['rating']] = (int)$row['c'];
    }
    return [
        'count'        => (int)$agg['c'],
        'average'      => round((float)$agg['a'], 1),
        'distribution' => $dist,
    ];
}

try {
    $pdo = db();

    if ($method === 'GET') {
        $params = [];
        $where  = "status = 'approved'";
        if (isset($_GET['rating'])) {
            $r = (int)$_GET['rating'];
            if ($r >= 1 && $r <= 5) {
                $where .= ' AND rating = ?';
                $params[] = $r;
            }
        }
        $limit = isset($_GET['limit']) ? max(1, min(200, (int)$_GET['limit'])) : 200;
        $stmt = $pdo->prepare(
            "SELECT id, name, vehicle, rating, comment AS text, created_at
             FROM reviews WHERE $where ORDER BY created_at DESC, id DESC LIMIT $limit"
        );
        $stmt->execute($params);
        $reviews = array_map(function ($r) {
            $r['id'] = (int)$r['id'];
            $r['rating'] = (int)$r['rating'];
            return $r;
        }, $stmt->fetchAll());

        json_out(array_merge(['reviews' => $reviews], aggregate($pdo)));
    }

    if ($method === 'POST') {
        $body    = read_json_body();
        $name    = trim((string)($body['name'] ?? ''));
        $vehicle = trim((string)($body['vehicle'] ?? ''));
        $rating  = (int)($body['rating'] ?? 0);
        $comment = trim((string)($body['comment'] ?? ($body['text'] ?? '')));

        $errors = [];
        if ($name === '' || mb_strlen($name) > 80) {
            $errors['name'] = 'Please enter your name.';
        }
        if ($rating < 1 || $rating > 5) {
            $errors['rating'] = 'Rating must be 1 to 5.';
        }
        if (mb_strlen($comment) < 4) {
            $errors['comment'] = 'Comment is too short.';
        }
        if (mb_strlen($comment) > 1000) {
            $errors['comment'] = 'Comment is too long (max 1000 characters).';
        }
        if ($vehicle !== '' && mb_strlen($vehicle) > 80) {
            $errors['vehicle'] = 'Vehicle name is too long.';
        }
        if ($errors) {
            json_out(['errors' => $errors], 422);
        }

        $stmt = $pdo->prepare("INSERT INTO reviews (name, vehicle, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $vehicle !== '' ? $vehicle : null, $rating, $comment]);

        $id  = (int)$pdo->lastInsertId();
        $row = $pdo->prepare("SELECT id, name, vehicle, rating, comment AS text, created_at FROM reviews WHERE id = ?");
        $row->execute([$id]);
        $review = $row->fetch();
        $review['id'] = (int)$review['id'];
        $review['rating'] = (int)$review['rating'];

        json_out(array_merge(['review' => $review], aggregate($pdo)), 201);
    }

    json_out(['error' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    json_out(['error' => 'Database error. Make sure MySQL is running and the database was imported.'], 500);
}
