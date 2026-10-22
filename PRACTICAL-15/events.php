
<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';

check_session_timeout();

if (!isset($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: LOGIN.html');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE :title OR organizer LIKE :organizer OR venue LIKE :venue)";
    $params['title'] = "%$search%";
    $params['organizer'] = "%$search%";
    $params['venue'] = "%$search%";
}

if ($category !== '') {
    $sql .= " AND category = :category";
    $params['category'] = $category;
}

$sql .= " ORDER BY event_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $conn->query(
    "SELECT DISTINCT category FROM events ORDER BY category"
)->fetchAll(PDO::FETCH_COLUMN);

$message = $_SESSION['event_message'] ?? '';
unset($_SESSION['event_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management | Student Hub</title>
    <link rel="stylesheet" href="events.css">
</head>
<body>
<header class="header">
    <h1>Event Management</h1>
    <p>Manage events and their posters</p>
</header>


<nav class="navbar">
    <div class="nav-links">
        <a href="admindash.php">Dashboard</a>
        <a href="students.php">Student Management</a>
        <a href="events.php" class="active">Event Management</a>
        <a href="audit_logs.php">Audit Logs</a>
    </div>

    <a href="logout.php" class="logout-btn">Logout</a>
</nav>


<main class="container">
    <?php if ($message !== ''): ?>
        <div class="message"><?= h($message) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="GET" class="filter-form">
            <input
                type="search"
                name="search"
                placeholder="Search title, organizer or venue"
                value="<?= h($search) ?>"
            >

            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $item): ?>
                    <option value="<?= h($item) ?>"
                        <?= $category === $item ? 'selected' : '' ?>>
                        <?= h($item) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Search</button>
            <a class="button secondary" href="events.php">Reset</a>
        </form>

        <a class="button" href="event_form.php">+ Add Event</a>
    </div>

    <p class="count">Total events: <?= count($events) ?></p>

    <div class="event-grid">
        <?php if (!$events): ?>
            <p>No events found. Click Add Event to create one.</p>
        <?php endif; ?>

        <?php foreach ($events as $event): ?>
            <article class="event-card">
                <?php if (!empty($event['poster'])): ?>
                    <img
                        class="poster"
                        src="uploads/<?= rawurlencode(basename($event['poster'])) ?>"
                        alt="<?= h($event['title']) ?> poster"
                    >
                <?php else: ?>
                    <div class="no-poster">No Poster Uploaded</div>
                <?php endif; ?>

                <div class="event-content">
                    <span class="category"><?= h($event['category']) ?></span>
                    <h2><?= h($event['title']) ?></h2>
                    <p><strong>Date:</strong> <?= h($event['event_date']) ?></p>
                    <p><strong>Time:</strong> <?= h($event['event_time']) ?></p>
                    <p><strong>Organizer:</strong> <?= h($event['organizer']) ?></p>
                    <p><strong>Venue:</strong> <?= h($event['venue']) ?></p>
                    <p><strong>Status:</strong> <?= h($event['status']) ?></p>

                    <div class="actions">
                        <a class="button" href="event_form.php?id=<?= (int)$event['id'] ?>">Edit</a>

                        <form method="POST" action="event_delete.php"
                              onsubmit="return confirm('Delete this event?')">
                            <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">
                            <input type="hidden" name="csrf_token"
                                   value="<?= h($_SESSION['csrf_token']) ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
