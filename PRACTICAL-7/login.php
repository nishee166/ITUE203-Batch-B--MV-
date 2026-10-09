<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: LOGIN.html");
    exit;
}

$studentId = trim($_POST["student_id"] ?? "");
$password = $_POST["password"] ?? "";

if ($studentId == "" || $password == "") {
    echo '<script>alert("Please enter Student ID and Password."); window.location.href="LOGIN.html";</script>';
    exit;
}

$jsonFile = __DIR__ . "/registrations.json";

if (!file_exists($jsonFile)) {
    echo '<script>alert("No registration data found. Please register first."); window.location.href="LOGIN.html";</script>';
    exit;
}

$data = json_decode(file_get_contents($jsonFile), true);

if (!is_array($data)) {
    echo '<script>alert("Registration data could not be read."); window.location.href="LOGIN.html";</script>';
    exit;
}

$loginSuccessful = false;

foreach ($data as $student) {
    if (
        isset($student["student_id"], $student["password"]) &&
        strcasecmp($student["student_id"], $studentId) == 0 &&
        password_verify($password, $student["password"])
    ) {
        $loginSuccessful = true;
        break;
    }
}

if ($loginSuccessful) {
    header("Location: INDEX.html");
    exit;
}

echo '<script>alert("Invalid Student ID or Password."); window.location.href="LOGIN.html";</script>';
exit;
?>
