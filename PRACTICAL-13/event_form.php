
<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';

check_session_timeout();

if (!isset($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: LOGIN.html');
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$uploadDir = __DIR__ . '/uploads/';

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    die('Unable to create uploads folder.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$editing = $id !== null && $id !== false && $id > 0;

$event = [
    'title' => '',
    'category' => '',
    'event_date' => '',
    'organizer' => '',
    'venue' => '',
    'event_time' => '',
    'status' => 'Upcoming',
    'poster' => ''
];

if ($editing) {
    $stmt = $conn->prepare('SELECT * FROM events WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        $_SESSION['event_message'] = 'Event not found.';
        header('Location: events.php');
        exit;
    }

    $event = array_merge($event, $existing);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Invalid request token. Please reload and try again.';
    } else {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'remove_poster') {
            if (!$editing) {
                $errors[] = 'Save the event before removing its poster.';
            } else {
                try {
                    $stmt = $conn->prepare('SELECT poster FROM events WHERE id = :id');
                    $stmt->execute(['id' => $id]);
                    $currentPoster = $stmt->fetchColumn();

                    if ($currentPoster === false) {
                        $_SESSION['event_message'] = 'Event not found.';
                        header('Location: events.php');
                        exit;
                    }

                    $stmt = $conn->prepare(
                        'UPDATE events SET poster = NULL WHERE id = :id'
                    );
                    $stmt->execute(['id' => $id]);

                    if (!empty($currentPoster)) {
                        $oldPath = $uploadDir . basename($currentPoster);

                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $_SESSION['event_message'] = 'Poster removed successfully.';
                    header('Location: event_form.php?id=' . $id);
                    exit;
                } catch (Throwable $e) {
                    $errors[] = 'Could not remove the poster. Please try again.';
                }
            }
        } else {
            $event['title'] = trim($_POST['title'] ?? '');
            $event['category'] = trim($_POST['category'] ?? '');
            $event['event_date'] = trim($_POST['event_date'] ?? '');
            $event['organizer'] = trim($_POST['organizer'] ?? '');
            $event['venue'] = trim($_POST['venue'] ?? '');
            $event['event_time'] = trim($_POST['event_time'] ?? '');
            $event['status'] = trim($_POST['status'] ?? 'Upcoming');

            if ($event['title'] === '' || strlen($event['title']) > 150) {
                $errors[] = 'Title is required and must be at most 150 characters.';
            }

            if ($event['category'] === '' || strlen($event['category']) > 50) {
                $errors[] = 'Enter a category of at most 50 characters.';
            }

            $date = DateTime::createFromFormat('!Y-m-d', $event['event_date']);

            if (!$date || $date->format('Y-m-d') !== $event['event_date']) {
                $errors[] = 'Select a valid event date.';
            }

            if ($event['organizer'] === '' || strlen($event['organizer']) > 100) {
                $errors[] = 'Organizer is required and must be at most 100 characters.';
            }

            if ($event['venue'] === '' || strlen($event['venue']) > 150) {
                $errors[] = 'Venue is required and must be at most 150 characters.';
            }

            if ($event['event_time'] === '' || strlen($event['event_time']) > 30) {
                $errors[] = 'Enter an event time of at most 30 characters.';
            }

            if ($event['status'] === '' || strlen($event['status']) > 50) {
                $errors[] = 'Enter a status of at most 50 characters.';
            }

            $newPoster = null;

            if (
                isset($_FILES['poster']) &&
                $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                $file = $_FILES['poster'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Poster upload failed. Please try again.';
                } elseif ($file['size'] > 3 * 1024 * 1024) {
                    $errors[] = 'Poster must be 3 MB or smaller.';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);

                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (
                        !isset($allowed[$mime]) ||
                        @getimagesize($file['tmp_name']) === false
                    ) {
                        $errors[] = 'Only valid JPG, PNG, or WebP images are allowed.';
                    } else {
                        $newPoster = bin2hex(random_bytes(16)) .
                            '.' . $allowed[$mime];
                    }
                }
            }

            if (!$errors) {
                $oldPoster = null;
                $newPosterPath = null;

                try {
                    if ($editing) {
                        $sql = 'UPDATE events SET
                                title = :title,
                                category = :category,
                                event_date = :event_date,
                                organizer = :organizer,
                                venue = :venue,
                                event_time = :event_time,
                                status = :status';
                    } else {
                        $sql = 'INSERT INTO events
                                (title, category, event_date, organizer,
                                 venue, event_time, status)
                                VALUES
                                (:title, :category, :event_date, :organizer,
                                 :venue, :event_time, :status)';
                    }

                    $params = [
                        'title' => $event['title'],
                        'category' => $event['category'],
                        'event_date' => $event['event_date'],
                        'organizer' => $event['organizer'],
                        'venue' => $event['venue'],
                        'event_time' => $event['event_time'],
                        'status' => $event['status']
                    ];

                    if ($editing) {
                        $sql .= ' WHERE id = :id';
                        $params['id'] = $id;
                    }

                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);

                    $eventId = $editing
                        ? $id
                        : (int)$conn->lastInsertId();

                    if ($newPoster !== null) {
                        $newPosterPath = $uploadDir . $newPoster;

                        if (!move_uploaded_file(
                            $_FILES['poster']['tmp_name'],
                            $newPosterPath
                        )) {
                            throw new RuntimeException(
                                'Could not save the uploaded poster.'
                            );
                        }

                        $stmt = $conn->prepare(
                            'SELECT poster FROM events WHERE id = :id'
                        );
                        $stmt->execute(['id' => $eventId]);
                        $oldPoster = $stmt->fetchColumn();

                        $stmt = $conn->prepare(
                            'UPDATE events SET poster = :poster WHERE id = :id'
                        );
                        $stmt->execute([
                            'poster' => $newPoster,
                            'id' => $eventId
                        ]);

                        if (!empty($oldPoster)) {
                            $oldPath = $uploadDir . basename($oldPoster);

                            if (is_file($oldPath)) {
                                unlink($oldPath);
                            }
                        }
                    }

                    $_SESSION['event_message'] = $editing
                        ? 'Event updated successfully.'
                        : 'Event added successfully.';

                    header('Location: events.php');
                    exit;
                } catch (Throwable $e) {
                    if (
                        $newPosterPath !== null &&
                        is_file($newPosterPath)
                    ) {
                        unlink($newPosterPath);
                    }

                    $errors[] = 'Could not save the event. Check your database and uploads folder.';
                }
            }
        }
    }
}

if (!empty($_SESSION['event_message'])) {
    $pageMessage = $_SESSION['event_message'];
    unset($_SESSION['event_message']);
} else {
    $pageMessage = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Edit Event' : 'Add Event' ?></title>
    <link rel="stylesheet" href="events.css">
</head>
<body>

<header class="header">
    <h1><?= $editing ? 'Edit Event' : 'Add New Event' ?></h1>
    <p>Enter event details and upload a poster</p>
</header>

<main class="container">

    <?php if ($pageMessage !== ''): ?>
        <div class="message"><?= h($pageMessage) ?></div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="message"><?= h($error) ?></div>
    <?php endforeach; ?>

    <form method="POST" enctype="multipart/form-data"
          class="event-card event-content">

        <input type="hidden" name="csrf_token"
               value="<?= h($_SESSION['csrf_token']) ?>">

        <label for="title">Event Title</label>
        <input id="title" name="title" maxlength="150" required
               value="<?= h($event['title']) ?>">

        <label for="category">Category</label>
        <input id="category" name="category" maxlength="50" required
               value="<?= h($event['category']) ?>"
               placeholder="Technical, Cultural, Sports">

        <label for="event_date">Event Date</label>
        <input id="event_date" type="date" name="event_date" required
               value="<?= h($event['event_date']) ?>">

        <label for="event_time">Event Time</label>
        <input id="event_time" name="event_time" maxlength="30" required
               value="<?= h($event['event_time']) ?>"
               placeholder="10:00 AM">

        <label for="organizer">Organizer</label>
        <input id="organizer" name="organizer" maxlength="100" required
               value="<?= h($event['organizer']) ?>">

        <label for="venue">Venue</label>
        <input id="venue" name="venue" maxlength="150" required
               value="<?= h($event['venue']) ?>">

        <label for="status">Status</label>
        <select id="status" name="status" required>
            <?php foreach (
                ['Upcoming', 'Ongoing', 'Completed', 'Cancelled'] as $status
            ): ?>
                <option value="<?= h($status) ?>"
                    <?= $event['status'] === $status ? 'selected' : '' ?>>
                    <?= h($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="poster">
            Poster (JPG, PNG, WebP; maximum 3 MB)
        </label>
        <input id="poster" type="file" name="poster"
               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

        <?php if (!empty($event['poster'])): ?>
            <p>Current poster:</p>

            <img class="poster"
                 src="uploads/<?= rawurlencode(basename($event['poster'])) ?>"
                 alt="Current event poster">

            <p>Want to remove this poster?</p>

            <button
                type="submit"
                name="action"
                value="remove_poster"
                formnovalidate
                onclick="return confirm('Are you sure you want to remove this poster?');"
                style="background-color:#dc3545;color:white;">
                Remove Poster
            </button>

            <p>Leave the upload empty to keep the current poster.</p>
        <?php endif; ?>

        <div class="actions">
            <button type="submit" name="action" value="save">
                <?= $editing ? 'Update Event' : 'Save Event' ?>
            </button>

            <a class="button secondary" href="events.php">Cancel</a>
        </div>

    </form>
</main>

</body>
</html>
