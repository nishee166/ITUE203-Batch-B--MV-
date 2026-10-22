
<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/audit_helper.php';

check_session_timeout();

if (
    empty($_SESSION['student_id']) ||
    strtolower($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: LOGIN.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (
    !isset($_SESSION['csrf_token']) ||
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
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
    $stmt = $conn->prepare(
        'SELECT id, title, category, poster
         FROM events
         WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $_SESSION['event_message'] = 'Event not found.';
        header('Location: events.php');
        exit;
    }

    $stmt = $conn->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() > 0) {
        writeAuditLog(
            $conn,
            'DELETE_EVENT',
            'event',
            (int)$event['id'],
            'Deleted event: ' . $event['title'] .
            ' (Category: ' . $event['category'] . ')'
        );

        if (!empty($event['poster'])) {
            $posterPath = __DIR__ . '/uploads/' .
                basename($event['poster']);

            if (is_file($posterPath)) {
                if (!unlink($posterPath)) {
                    error_log(
                        'Could not delete poster file for event ID ' . $id
                    );
                }
            }
        }

        $_SESSION['event_message'] = 'Event deleted successfully.';
    } else {
        $_SESSION['event_message'] = 'Event could not be deleted.';
    }
} catch (Throwable $e) {
    error_log('Event deletion failed: ' . $e->getMessage());
    $_SESSION['event_message'] =
        'Could not delete the event. Please try again.';
}

header('Location: events.php');
exit;
