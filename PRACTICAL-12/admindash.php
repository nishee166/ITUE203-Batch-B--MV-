
<?php
require_once __DIR__ . '/includes/helpers.php';

check_session_timeout();

if (
    empty($_SESSION['student_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: LOGIN.html');
    exit;
}

$adminId = htmlspecialchars(
    (string) $_SESSION['student_id'],
    ENT_QUOTES,
    'UTF-8'
);

$adminName = htmlspecialchars(
    (string) ($_SESSION['student_name'] ?? 'Admin'),
    ENT_QUOTES,
    'UTF-8'
);
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

        <p>
            Manage your Student Hub Portal from one place.
            Manage student records, organize events, and keep your account secure.
        </p>

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
        <h2>Admin Dashboard</h2>

        <p class="section-description">
            Manage your account, student records, and college events from one place.
        </p>

        <div class="dashboard-grid">

            <!-- Admin Profile -->
            <div class="card">
                <div class="card-icon">👤</div>

                <h3>Admin Profile</h3>

                <p>
                    View your administrator account details,
                    ID, and current role.
                </p>

                <a href="#profile">View Details →</a>
            </div>

            <!-- Student Management -->
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

            <!-- Event Management: Practical 12 -->
            <a href="events.php"
               class="card clickable-card"
               aria-label="Open Event Management">

                <div class="card-icon">🎉</div>

                <h3>Event Management</h3>

                <p>
                    Add, view, edit, search, and delete college events.
                    Upload event posters with file type and size validation.
                </p>

                <span class="card-link">Manage Events →</span>
            </a>

        </div>
    </section>

    <section class="security-note" id="profile">
        <strong>Security Reminder:</strong>
        Keep your password private and log out when using a shared computer.
    </section>

</main>

<footer>
    <p>&copy; 2026 Student Hub Portal | Your One-Stop Learning Platform</p>
</footer>

</body>
</html>
