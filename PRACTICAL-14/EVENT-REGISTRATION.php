<?php

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: LOGIN.html');
    exit;
}

$loggedInStudentId = $_SESSION['student_id'];

$eventTitle = trim($_GET['event'] ?? '');

if ($eventTitle === '') {
    die("Invalid event.");
}

$stmt = $conn->prepare(
    "SELECT id, title, category, event_date, organizer, venue, event_time, status
     FROM events
     WHERE title = :title"
);

$stmt->execute([
    ':title' => $eventTitle
]);

$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

$studentStmt = $conn->prepare(
    "SELECT id, student_id, student_name
     FROM students
     WHERE student_id = :student_id"
);

$studentStmt->execute([
    ':student_id' => $loggedInStudentId
]);

$student = $studentStmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student account not found.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $studentName = clean_text($_POST['student_name'] ?? '');
    $studentIdForm = clean_text($_POST['student_id'] ?? '');
    $email = clean_text($_POST['email'] ?? '');

    if ($studentName === '' || $studentIdForm === '' || $email === '') {

        $message = "Please fill all the fields.";

    } elseif ($studentIdForm !== $student['student_id']) {

        $message = "Student ID does not match the logged-in account.";

    } else {

        $check = $conn->prepare(
            "SELECT id
             FROM registrations
             WHERE student_id = :student_id
             AND event_id = :event_id"
        );

        $check->execute([
            ':student_id' => $student['id'],
            ':event_id' => $event['id']
        ]);

        if ($check->fetch()) {

            $message = "You have already registered for this event.";

        } else {

            $insert = $conn->prepare(
                "INSERT INTO registrations (student_id, event_id)
                 VALUES (:student_id, :event_id)"
            );

            $insert->execute([
                ':student_id' => $student['id'],
                ':event_id' => $event['id']
            ]);

            $message = "Registration successful!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Event Registration - Student Hub Portal</title>

<link rel="stylesheet" href="EVENT-REGISTRATION.css">

</head>

<body>

<header>

    <h1>Student Hub Portal</h1>

    <p>Your One-Stop Learning Platform</p>

</header>

<section class="registration-section">

    <div class="registration-container">

        <div class="registration-icon">
            📝
        </div>

        <h2>Event Registration</h2>

        <p class="registration-subtitle">
            Register for the selected event
        </p>

        <?php if ($message !== ''): ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <div class="event-details">

            <h3>
                <?php echo htmlspecialchars($event['title']); ?>
            </h3>

            <p>
                <strong>Category:</strong>
                <?php echo htmlspecialchars($event['category']); ?>
            </p>

            <p>
                <strong>Date:</strong>
                <?php echo htmlspecialchars($event['event_date']); ?>
            </p>

            <p>
                <strong>Organizer:</strong>
                <?php echo htmlspecialchars($event['organizer']); ?>
            </p>

            <p>
                <strong>Venue:</strong>
                <?php echo htmlspecialchars($event['venue']); ?>
            </p>

            <p>
                <strong>Time:</strong>
                <?php echo htmlspecialchars($event['event_time']); ?>
            </p>

        </div>

        <?php if ($message !== "Registration successful!"): ?>

        <form method="POST">

            <div class="input-group">

                <label for="student_name">
                    Student Name
                </label>

                <input
                    type="text"
                    id="student_name"
                    name="student_name"
                    placeholder="Enter your name"
                    value="<?php echo htmlspecialchars($student['student_name']); ?>"
                    required
                >

            </div>

            <div class="input-group">

                <label for="student_id">
                    Student ID
                </label>

                <input
                    type="text"
                    id="student_id"
                    name="student_id"
                    placeholder="Enter your Student ID"
                    value="<?php echo htmlspecialchars($student['student_id']); ?>"
                    required
                >

            </div>

            <div class="input-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <button type="submit">
                Register for Event
            </button>

        </form>

        <?php endif; ?>

        <a href="EVENTS.html" class="back-link">
            ← Back to Events
        </a>

    </div>

</section>

<footer>

    <p>
        © 2026 Student Hub Portal | All Rights Reserved
    </p>

</footer>

</body>

</html>