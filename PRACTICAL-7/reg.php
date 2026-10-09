<?php

$errors = [];

$old = [
    "student_id" => "",
    "student_name" => "",
    "department" => "",
    "email" => ""
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $old["student_id"] = trim($_POST["student_id"] ?? "");
    $old["student_name"] = trim($_POST["student_name"] ?? "");
    $old["department"] = trim($_POST["department"] ?? "");
    $old["email"] = trim($_POST["email"] ?? "");

    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $terms = isset($_POST["terms"]);

    if ($old["student_id"] == "") {
        $errors[] = "Student ID is required.";
    } elseif (!preg_match("/^[A-Za-z0-9]+$/", $old["student_id"])) {
        $errors[] = "Student ID can contain only letters and numbers.";
    }

    if ($old["student_name"] == "") {
        $errors[] = "Student Name is required.";
    } elseif (!preg_match("/^[A-Za-z ]+$/", $old["student_name"])) {
        $errors[] = "Student Name can contain only letters and spaces.";
    }

    if ($old["department"] == "") {
        $errors[] = "Department is required.";
    } elseif (!preg_match("/^[A-Za-z ]+$/", $old["department"])) {
        $errors[] = "Department can contain only letters and spaces.";
    }

    if ($old["email"] == "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($old["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($password == "") {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 10) {
        $errors[] = "Password must contain at least 10 characters.";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match("/[^A-Za-z0-9]/", $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    if ($confirmPassword == "") {
        $errors[] = "Confirm Password is required.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Password and Confirm Password do not match.";
    }

    if (!$terms) {
        $errors[] = "You must accept the terms and conditions.";
    }

    $jsonFile = __DIR__ . "/registrations.json";
    $csvFile = __DIR__ . "/registrations.csv";

    if (empty($errors)) {

        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $existingData = json_decode($jsonContent, true);

            if (!is_array($existingData)) {
                $existingData = [];
            }
        } else {
            $existingData = [];
        }

        foreach ($existingData as $student) {

            if (
                isset($student["student_id"]) &&
                strcasecmp($student["student_id"], $old["student_id"]) == 0
            ) {
                $errors[] = "This Student ID is already registered.";
                break;
            }

            if (
                isset($student["email"]) &&
                strcasecmp($student["email"], $old["email"]) == 0
            ) {
                $errors[] = "This email is already registered.";
                break;
            }
        }
    }

    if (empty($errors)) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $newStudent = [
            "student_id" => $old["student_id"],
            "student_name" => $old["student_name"],
            "department" => $old["department"],
            "email" => $old["email"],
            "password" => $hashedPassword,
            "registered_at" => date("Y-m-d H:i:s")
        ];

        $existingData[] = $newStudent;

        $jsonSaved = file_put_contents(
            $jsonFile,
            json_encode($existingData, JSON_PRETTY_PRINT)
        );

        if ($jsonSaved === false) {
            $errors[] = "Registration data could not be saved in JSON file.";
        }

        if (empty($errors)) {

            $csv = fopen($csvFile, "a");

            if ($csv === false) {

                $errors[] = "Registration data could not be saved in CSV file.";

            } else {

                if (filesize($csvFile) == 0) {

                    fputcsv($csv, [
                        "student_id",
                        "student_name",
                        "department",
                        "email",
                        "password",
                        "registered_at"
                    ]);
                }

                fputcsv($csv, [
                    $newStudent["student_id"],
                    $newStudent["student_name"],
                    $newStudent["department"],
                    $newStudent["email"],
                    $newStudent["password"],
                    $newStudent["registered_at"]
                ]);

                fclose($csv);

                echo "<script>
                    alert('Registration successful! Your data has been saved.');
                    window.location.href = 'LOGIN.html';
                </script>";
                exit;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registration - Student Hub Portal</title>

    <link rel="stylesheet" href="reg.css">
    <link rel="stylesheet" href="php-forms.css">

</head>

<body>

<header>

    <h1>Student Hub Portal</h1>

    <p>Your One-Stop Learning Platform</p>

</header>

<section class="registration-section">

    <div class="registration-container">

        <div class="registration-icon">📝</div>

        <h2>Create Your Account</h2>

        <p class="registration-subtitle">
            Register for your Student Hub Portal account
        </p>

        <?php if (!empty($errors)): ?>

            <div class="alert alert-error">

                <h4>Please correct the following errors:</h4>

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= htmlspecialchars($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <form
            id="registrationForm"
            action="reg.php"
            method="POST"
            novalidate
        >

            <div class="input-group">

                <label for="student-id">
                    Student ID
                </label>

                <input
                    type="text"
                    id="student-id"
                    name="student_id"
                    placeholder="Enter Student ID"
                    value="<?= htmlspecialchars($old["student_id"]) ?>"
                    required
                >

            </div>

            <div class="input-group">

                <label for="student-name">
                    Student Name
                </label>

                <input
                    type="text"
                    id="student-name"
                    name="student_name"
                    placeholder="Enter Student Name"
                    value="<?= htmlspecialchars($old["student_name"]) ?>"
                    required
                >

            </div>

            <div class="input-group">

                <label for="department">
                    Department
                </label>

                <input
                    type="text"
                    id="department"
                    name="department"
                    placeholder="Enter Department"
                    value="<?= htmlspecialchars($old["department"]) ?>"
                    required
                >

            </div>

            <div class="input-group">

                <label for="email">
                    Email ID
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter Email ID"
                    value="<?= htmlspecialchars($old["email"]) ?>"
                    required
                >

            </div>

            <div class="input-group">

                <label for="password">
                    Create Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create Password"
                    required
                >

            </div>

            <div class="input-group">

                <label for="confirm-password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    id="confirm-password"
                    name="confirm_password"
                    placeholder="Confirm Password"
                    required
                >

            </div>

            <div class="terms">

                <input
                    type="checkbox"
                    id="terms"
                    name="terms"
                    required
                >

                <label for="terms">
                    I agree to the terms and conditions
                </label>

            </div>

            <button type="submit">
                Create Account
            </button>

        </form>

        <p class="login-text">

            Already have an account?

            <a href="LOGIN.html">
                Login Here
            </a>

        </p>

    </div>

</section>

<footer>

    <p>
        © 2026 Student Hub Portal | All Rights Reserved
    </p>

</footer>

<script src="reg.js"></script>

</body>

</html>