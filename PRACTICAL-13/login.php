
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

if (
    !$student ||
    !password_verify($password, $student['password'])
) {
    render_result_page(
        'Login Failed',
        'error',
        ['Invalid Student ID or Password.'],
        'LOGIN.html',
        'Try Again'
    );
    exit;
}

$studentId = strtoupper(trim($student['student_id']));
$role = strtolower(trim($student['role'] ?? ''));

$isAdminId = preg_match('/^ADMIN\d{3}$/', $studentId) === 1;

if ($isAdminId && $role !== 'admin') {
    render_result_page(
        'Login Failed',
        'error',
        ['This admin-format account is not authorized as an administrator. Please contact the administrator.'],
        'LOGIN.html',
        'Go to Login'
    );
    exit;
}

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

regenerate_session_after_login();

$_SESSION['student_id'] = $student['student_id'];
$_SESSION['student_name'] = $student['student_name'];
$_SESSION['role'] = $role;
$_SESSION['last_activity'] = time();

if ($isAdminId && $role === 'admin') {
    header('Location: admindash.php');
    exit;
}

if ($role === 'student') {
    header('Location: home.php');
    exit;
}

render_result_page(
    'Login Failed',
    'error',
    ['Invalid account configuration. Please contact the administrator.'],
    'LOGIN.html',
    'Go to Login'
);
exit;
?>
