
<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

try {
    $stmt = $conn->prepare(
        "SELECT id, title, category, event_date, organizer,
                venue, event_time, status, poster
         FROM events
         ORDER BY event_date ASC, id DESC"
    );

    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'events' => $events
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load events right now.'
    ]);
}
