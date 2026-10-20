
<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';

check_session_timeout();

if (!isset($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: LOGIN.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (!isset($_SESSION['csrf_token']) ||
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'], $token)) {
    $_SESSION['event_message'] = 'Invalid request. Please try again.';
    header('Location: events.php');
    exit;
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION['event_message'] = 'Invalid event ID.';
    header('Location: events.php');
    exit;
}

try {
    $stmt = $conn->prepare('SELECT poster FROM events WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $_SESSION['event_message'] = 'Event not found.';
        header('Location: events.php');
        exit;
    }

    $stmt = $conn->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if (!empty($event['poster'])) {
        $posterPath = __DIR__ . '/uploads/' . basename($event['poster']);

        if (is_file($posterPath)) {
            unlink($posterPath);
        }
    }

    $_SESSION['event_message'] = 'Event deleted successfully.';
} catch (Throwable $e) {
    $_SESSION['event_message'] = 'Could not delete the event. Please try again.';
}

header('Location: events.php');
exit;
