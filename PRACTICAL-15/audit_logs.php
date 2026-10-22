
<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';

check_session_timeout();

if (
    empty($_SESSION['student_id']) ||
    strtolower($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: LOGIN.html');
    exit;
}

$stmt = $conn->query(
    "SELECT id, user_id, user_name, action, entity_type,
            entity_id, details, ip_address, created_at
     FROM audit_logs
     ORDER BY id DESC
     LIMIT 200"
);

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function logEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$adminName = logEscape($_SESSION['student_name'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Audit Logs | Student Hub Portal</title>

    <link rel="stylesheet" href="audit_logs.css?v=2">
</head>

<body>

<header class="header">
    <h1>Welcome to Student Hub Portal</h1>
    <p>Administrator Audit Logs</p>
</header>

<nav class="navbar">
    <a href="admindash.php">Dashboard</a>
    <a href="students.php">Student Management</a>
    <a href="events.php">Event Management</a>
    <a href="audit_logs.php" class="active">Audit Logs</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</nav>

<main class="container">

    <section class="page-intro">
        <p class="eyebrow">ADMINISTRATOR PANEL</p>
        <h2>Admin Activity History</h2>
        <p>
            Welcome, <?= $adminName ?>. Monitor important actions
            performed in your Student Hub Portal.
        </p>
    </section>

    <section class="audit-panel">
        <div class="panel-heading">
            <div>
                <h2>Audit Log Records</h2>
                <p class="audit-description">
                    The latest 200 recorded activities from your database.
                </p>
            </div>

            <div class="record-count">
                <span>Total Records</span>
                <strong><?= count($logs) ?></strong>
            </div>
        </div>

        <?php if (count($logs) > 0): ?>

            <div class="table-wrapper">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>User Name</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Entity ID</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Date and Time</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= logEscape($log['id']) ?></td>
                                <td><?= logEscape($log['user_id']) ?></td>
                                <td><?= logEscape($log['user_name']) ?></td>
                                <td>
                                    <span class="action-badge">
                                        <?= logEscape($log['action']) ?>
                                    </span>
                                </td>
                                <td><?= logEscape($log['entity_type']) ?></td>
                                <td><?= logEscape($log['entity_id']) ?></td>
                                <td><?= logEscape($log['details']) ?></td>
                                <td><?= logEscape($log['ip_address']) ?></td>
                                <td><?= logEscape($log['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No Audit Records Yet</h3>
                <p>
                    Your audit log is ready. Records will appear here
                    when audit logging is connected to admin actions.
                </p>
            </div>

        <?php endif; ?>
    </section>

</main>

<footer>
    <p>&copy; <?= date('Y') ?> Student Hub Portal | Practical 14</p>
</footer>

</body>
</html>
