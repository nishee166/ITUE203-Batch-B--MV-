<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: LOGIN.html');
    exit;
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Hub Portal | Study Materials, Coding & Resources</title>

    <link rel="stylesheet" href="INDEX.css">

</head>

<body>

<header>

    <a href="LOGIN.html" class="logout-btn">Log out</a>

    <button id="themeBtn">🌙 Dark Mode</button>

    <h1>Welcome to Student Hub Portal</h1>

    <h2>Your One-Stop Learning Platform</h2>

</header>


<div class="navigation">

    <a href="home.php">Home</a>

    <a href="ABOUT.html">About</a>

    <a href="HACKATHON.html">Hackathon</a>

    <a href="STUDY.html">Study Material</a>

    <a href="CODING.html">Coding Practice</a>

    <a href="EVENTS.html">Events</a>

    <a href="PORTFOLIO.html">Portfolio</a>

    <a href="CONTACT.php">Contact</a>

    <a href="FAQ.html">FAQ</a>

    

</div>


<footer>

    <p>&copy; 2026 Student Hub Portal | All Rights Reserved</p>

</footer>


<script src="INDEX.js"></script>

</body>

</html>