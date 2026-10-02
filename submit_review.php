<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$message = trim($_POST['message'] ?? '');
$rating = (int) ($_POST['rating'] ?? 5);

if ($name === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Заповніть усі поля']);
    exit;
}

$rating = max(1, min(5, $rating));

$storageFile = __DIR__ . DIRECTORY_SEPARATOR . 'reviews.json';
$reviews = [];

if (file_exists($storageFile)) {
    $decoded = json_decode((string) file_get_contents($storageFile), true);
    if (is_array($decoded)) {
        $reviews = $decoded;
    }
}

$review = [
    'name' => mb_substr(strip_tags($name), 0, 60),
    'message' => mb_substr(strip_tags($message), 0, 600),
    'rating' => $rating,
    'created_at' => date('c'),
];

array_unshift($reviews, $review);
file_put_contents(
    $storageFile,
    json_encode($reviews, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
    LOCK_EX
);

echo json_encode([
    'ok' => true,
    'review' => [
        'name' => $review['name'],
        'message' => $review['message'],
        'rating' => str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']),
        'time' => 'щойно',
    ],
], JSON_UNESCAPED_UNICODE);
