<?php

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';

$errors = [];

$old = [
    'student_id' => '',
    'student_name' => '',
    'department' => '',
    'email' => ''
];

$flash = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {

        http_response_code(403);

        $errors[] = 'Security check failed. Please reload the page and try again.';

    } else {

        $old['student_id'] = clean_text($_POST['student_id'] ?? '');
        $old['student_name'] = clean_text($_POST['student_name'] ?? '');
        $old['department'] = clean_text($_POST['department'] ?? '');
        $old['email'] = clean_text($_POST['email'] ?? '');

        $password = is_string($_POST['password'] ?? null)
            ? $_POST['password']
            : '';

        $confirmPassword = is_string($_POST['confirm_password'] ?? null)
            ? $_POST['confirm_password']
            : '';

        $terms = isset($_POST['terms']);


        if ($old['student_id'] === '') {

            $errors[] = 'Student ID is required.';

        } elseif (!preg_match('/^[A-Za-z0-9]{3,20}$/', $old['student_id'])) {

            $errors[] = 'Invalid Student ID. Use 3-20 letters or numbers only.';
        }


        if ($old['student_name'] === '') {

            $errors[] = 'Student Name is required.';

        } elseif (!preg_match('/^[A-Za-z ]{2,60}$/', $old['student_name'])) {

            $errors[] = 'Invalid Student Name. Use 2-60 letters and spaces only.';
        }


        if ($old['department'] === '') {

            $errors[] = 'Department is required.';

        } elseif (!preg_match('/^[A-Za-z ]{2,60}$/', $old['department'])) {

            $errors[] = 'Invalid Department. Use 2-60 letters and spaces only.';
        }


        if ($old['email'] === '') {

            $errors[] = 'Email ID is required.';

        } elseif (
            strlen($old['email']) > 100 ||
            !filter_var($old['email'], FILTER_VALIDATE_EMAIL)
        ) {

            $errors[] = 'Please enter a valid email address.';
        }


        if ($password === '') {

            $errors[] = 'Password is required.';

        } elseif (strlen($password) < 10) {

            $errors[] = 'Password must contain at least 10 characters.';

        } elseif (strlen($password) > 72) {

            $errors[] = 'Password must not be longer than 72 characters.';

        } elseif (!preg_match('/[a-z]/', $password)) {

            $errors[] = 'Password must contain at least one lowercase letter.';

        } elseif (!preg_match('/[A-Z]/', $password)) {

            $errors[] = 'Password must contain at least one uppercase letter.';

        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {

            $errors[] = 'Password must contain at least one special character.';
        }


        if ($confirmPassword === '') {

            $errors[] = 'Confirm Password is required.';

        } elseif ($password !== $confirmPassword) {

            $errors[] = 'Password and Confirm Password do not match.';
        }


        if (!$terms) {

            $errors[] = 'You must accept the terms and conditions.';
        }


        if (empty($errors)) {

            $stmt = $conn->prepare(
                "SELECT id
                 FROM students
                 WHERE student_id = :student_id
                 OR email = :email"
            );

            $stmt->execute([
                ':student_id' => $old['student_id'],
                ':email' => $old['email']
            ]);

            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {

                $errors[] = 'This Student ID or email is already registered.';
            }
        }


        if (empty($errors)) {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO students
                (student_id, student_name, department, email, password)
                VALUES
                (:student_id, :student_name, :department, :email, :password)"
            );

            $stmt->execute([
                ':student_id' => $old['student_id'],
                ':student_name' => $old['student_name'],
                ':department' => $old['department'],
                ':email' => $old['email'],
                ':password' => $hashedPassword
            ]);

            csrf_rotate();

            echo '<script>
                alert("Registration successful!");
                window.location.href = "LOGIN.html";
            </script>';

            exit;
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


        <?php if ($flash): ?>

            <div
                class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>"
                role="status"
            >

                <p><?= e($flash['message']) ?></p>

                <p>
                    <a href="LOGIN.html">
                        Go to Login &rarr;
                    </a>
                </p>

            </div>

        <?php endif; ?>


        <?php if (!empty($errors)): ?>

            <div class="alert alert-error" role="alert">

                <h4>Please correct the following errors:</h4>

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li><?= e($error) ?></li>

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

            <?= csrf_field() ?>


            <div class="input-group">

                <label for="student-id">
                    Student ID
                </label>

                <input
                    type="text"
                    id="student-id"
                    name="student_id"
                    placeholder="Enter Student ID"
                    value="<?= e($old['student_id']) ?>"
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
                    value="<?= e($old['student_name']) ?>"
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
                    value="<?= e($old['department']) ?>"
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
                    value="<?= e($old['email']) ?>"
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