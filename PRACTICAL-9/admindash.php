
<?php
require_once __DIR__ . '/includes/helpers.php';

if (
    !isset($_SESSION['student_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: LOGIN.html');
    exit;
}

$adminId = htmlspecialchars(
    $_SESSION['student_id'],
    ENT_QUOTES,
    'UTF-8'
);

$adminName = htmlspecialchars(
    $_SESSION['student_name'] ?? 'Admin',
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
    <a href="INDEX.html">Home</a>
    <a href="INDEX.html#about">About</a>
    <a href="admindash.php" class="active">Dashboard</a>
    <a href="EVENTS.html">Events</a>
    <a href="logout.php">Logout</a>
</nav>

<main class="container">

    <section class="welcome">
        <p class="eyebrow">ADMINISTRATOR PANEL</p>

        <h2>Welcome back, <span><?= $adminName ?></span>!</h2>

        <p>
            Manage your Student Hub Portal from one place.
            Access portal sections and keep your account secure.
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
            Quick access to your Student Hub Portal.
        </p>

        <div class="dashboard-grid">

            <div class="card">
                <div class="card-icon">👤</div>
                <h3>Admin Profile</h3>
                <p>
                    View your administrator account details,
                    ID, and current role.
                </p>
                <a href="#profile">View Details →</a>
            </div>

            <div class="card">
                <div class="card-icon">📅</div>
                <h3>Events</h3>
                <p>
                    Explore events and activities available
                    on the Student Hub Portal.
                </p>
                <a href="EVENTS.html">Explore Events →</a>
            </div>

            <div class="card">
                <div class="card-icon">🌐</div>
                <h3>Student Hub Website</h3>
                <p>
                    Return to the main website and explore
                    learning resources.
                </p>
                <a href="INDEX.html">Visit Website →</a>
            </div>

            <div class="card">
                <div class="card-icon">🔐</div>
                <h3>Secure Logout</h3>
                <p>
                    Safely end your administrator session
                    after completing your work.
                </p>
                <a href="logout.php">Logout Now →</a>
            </div>

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

