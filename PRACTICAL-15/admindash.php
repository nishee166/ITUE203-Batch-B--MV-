
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

function dashboardEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function getDashboardCount(PDO $conn, string $sql): int
{
    return (int) $conn->query($sql)->fetchColumn();
}

$adminId = dashboardEscape($_SESSION['student_id']);
$adminName = dashboardEscape($_SESSION['student_name'] ?? 'Admin');

$totalUsers = 0;
$totalStudents = 0;
$totalAdmins = 0;
$totalEvents = 0;
$totalRegistrations = 0;
$latestRegistrations = [];
$dashboardError = '';

try {
    $totalUsers = getDashboardCount(
        $conn,
        "SELECT COUNT(*) FROM students"
    );

    $totalStudents = getDashboardCount(
        $conn,
        "SELECT COUNT(*) FROM students WHERE LOWER(role) = 'student'"
    );

    $totalAdmins = getDashboardCount(
        $conn,
        "SELECT COUNT(*) FROM students WHERE LOWER(role) = 'admin'"
    );

    $totalEvents = getDashboardCount(
        $conn,
        "SELECT COUNT(*) FROM events"
    );

    $totalRegistrations = getDashboardCount(
        $conn,
        "SELECT COUNT(*) FROM registrations"
    );

    $stmt = $conn->query(
        "SELECT
            r.id,
            r.registration_date,
            s.student_name,
            s.student_id,
            e.title AS event_title
         FROM registrations r
         LEFT JOIN students s ON r.student_id = s.id
         LEFT JOIN events e ON r.event_id = e.id
         ORDER BY r.registration_date DESC, r.id DESC
         LIMIT 10"
    );

    $latestRegistrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Admin dashboard error: ' . $e->getMessage());
    $dashboardError = 'Dashboard information could not be loaded. Please check your database.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Student Hub Portal</title>

    <link rel="stylesheet" href="admindash.css">
</head>
<body>

<header class="header">
    <h1>Welcome to Student Hub Portal</h1>
    <p>Your One-Stop Learning Platform</p>
</header>

<nav class="navbar">
    <a href="logout.php" class="logout-btn">Logout</a>
</nav>

<main class="container">

    <section class="welcome">
        <p class="eyebrow">ADMINISTRATOR PANEL</p>

        <h2>Welcome back, <span><?= $adminName ?></span>!</h2>

       

        <div class="details">
            <div class="detail-box">
                <span>Admin ID</span>
                <strong><?= $adminId ?></strong>
            </div>

            <div class="detail-box">
                <span>Account Role</span>
                <strong>Administrator</strong>
            </div>

            <div class="detail-box">
                <span>Session Status</span>
                <strong>Logged In</strong>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <h2>Dashboard Statistics</h2>

       

        <?php if ($dashboardError !== ''): ?>

            <div class="dashboard-error">
                <?= dashboardEscape($dashboardError) ?>
            </div>

        <?php else: ?>

            <div class="stats-grid">

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <h3>Total Users</h3>
                    <p class="stat-number"><?= $totalUsers ?></p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🎓</div>
                    <h3>Total Students</h3>
                    <p class="stat-number"><?= $totalStudents ?></p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🛡️</div>
                    <h3>Total Admins</h3>
                    <p class="stat-number"><?= $totalAdmins ?></p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🎉</div>
                    <h3>Total Events</h3>
                    <p class="stat-number"><?= $totalEvents ?></p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <h3>Total Registrations</h3>
                    <p class="stat-number"><?= $totalRegistrations ?></p>
                </div>

            </div>

            <section class="registrations-section">
                <h2>Latest Event Registrations</h2>

                <p class="section-description">
                    The ten most recent event registrations.
                </p>

                <?php if (count($latestRegistrations) > 0): ?>

                    <div class="table-wrapper">
                        <table class="registration-table">
                            <thead>
                                <tr>
                                    <th>Registration ID</th>
                                    <th>Student Name</th>
                                    <th>Student ID</th>
                                    <th>Event Name</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($latestRegistrations as $registration): ?>
                                    <tr>
                                        <td><?= dashboardEscape($registration['id']) ?></td>
                                        <td><?= dashboardEscape($registration['student_name'] ?? 'Unknown') ?></td>
                                        <td><?= dashboardEscape($registration['student_id'] ?? 'N/A') ?></td>
                                        <td><?= dashboardEscape($registration['event_title'] ?? 'Event unavailable') ?></td>
                                        <td><?= dashboardEscape($registration['registration_date']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <p class="empty-message">
                        No event registrations are available yet.
                    </p>

                <?php endif; ?>
            </section>

        <?php endif; ?>
    </section>

    <section class="dashboard-section management-section">
        <h2>Management Options</h2>

       
        <div class="dashboard-grid">

            <div class="card" id="profile">
                <div class="card-icon">👤</div>
                <h3>Admin Profile</h3>
                <p>
                    View your administrator account details,
                    ID, and current role.
                </p>
                <a href="#profile">View Details →</a>
            </div>

            <a href="students.php"
               class="card clickable-card"
               aria-label="Open Student Management">

                <div class="card-icon">👥</div>
                <h3>Student Management</h3>
                <p>
                    Add, view, edit, delete, search, filter,
                    and sort student records.
                </p>
                <span class="card-link">Manage Students →</span>
            </a>

            <a href="events.php"
               class="card clickable-card"
               aria-label="Open Event Management">

                <div class="card-icon">🎉</div>
                <h3>Event Management</h3>
                <p>
                    Add, view, edit, search, and delete college events.
                    Manage event details and posters.
                </p>
                <span class="card-link">Manage Events →</span>
            </a>

            <a href="audit_logs.php"
               class="card clickable-card"
               aria-label="Open Audit Logs">

                <div class="card-icon">📋</div>
                <h3>Audit Logs</h3>
                <p>
                    Review the recorded administrator activities
                    and changes made to the portal.
                </p>
                <span class="card-link">View Audit Logs →</span>
            </a>

        </div>
    </section>

    <section class="security-note">
        <strong>Security Reminder:</strong>
        Keep your password private and log out when using a shared computer.
    </section>

</main>

<footer>
    <p>&copy; 2026 Student Hub Portal | Your One-Stop Learning Platform</p>
</footer>

</body>
</html>
