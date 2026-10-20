
<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    render_result_page(
        'Invalid Request',
        'error',
        ['This page accepts form submissions only.'],
        'LOGIN.html',
        'Go to Login'
    );
    exit;
}

$studentId = strtoupper(clean_text($_POST['student_id'] ?? ''));

$password = is_string($_POST['password'] ?? null)
    ? $_POST['password']
    : '';

if ($studentId === '' || $password === '') {
    render_result_page(
        'Login Failed',
        'error',
        ['Student ID and Password are required.'],
        'LOGIN.html',
        'Try Again'
    );
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, student_id, student_name, password, role
     FROM students
     WHERE student_id = :student_id
     LIMIT 1"
);

$stmt->execute([
    ':student_id' => $studentId
]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student || !password_verify($password, $student['password'])) {
    render_result_page(
        'Login Failed',
        'error',
        ['Invalid Student ID or Password.'],
        'LOGIN.html',
        'Try Again'
    );
    exit;
}

$role = strtolower(trim($student['role'] ?? ''));

if ($role !== 'admin' && $role !== 'student') {
    render_result_page(
        'Login Failed',
        'error',
        ['Your account role is missing or invalid. Please contact the administrator.'],
        'LOGIN.html',
        'Try Again'
    );
    exit;
}

session_regenerate_id(true);

$_SESSION['student_id'] = $student['student_id'];
$_SESSION['student_name'] = $student['student_name'];
$_SESSION['role'] = $role;

if ($role === 'admin') {
    header('Location: admindash.php');
    exit;
}

header('Location: home.php');
exit;
?>
