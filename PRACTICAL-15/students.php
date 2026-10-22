
<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/audit_helper.php';

check_session_timeout();

if (
    empty($_SESSION['student_id']) ||
    strtolower($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: LOGIN.html');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function redirectStudents(string $message, string $type = 'success'): void
{
    $_SESSION['student_message'] = $message;
    $_SESSION['student_message_type'] = $type;
    header('Location: students.php');
    exit;
}

$columns = ['student_id', 'student_name', 'department', 'email'];
$search = trim($_GET['search'] ?? '');
$department = trim($_GET['department'] ?? '');
$sort = $_GET['sort'] ?? 'student_name';
$order = strtoupper($_GET['order'] ?? 'ASC');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;

if (!in_array($sort, $columns, true)) {
    $sort = 'student_name';
}

if (!in_array($order, ['ASC', 'DESC'], true)) {
    $order = 'ASC';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        redirectStudents('Security check failed. Please try again.', 'error');
    }

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'delete') {
            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                redirectStudents('Invalid student record.', 'error');
            }

            $find = $conn->prepare(
                "SELECT id, student_id, student_name
                 FROM students
                 WHERE id = :id
                 LIMIT 1"
            );

            $find->execute([':id' => $id]);
            $target = $find->fetch(PDO::FETCH_ASSOC);

            if (!$target) {
                redirectStudents('Student record not found.', 'error');
            }

            if ((string)$target['student_id'] === (string)$_SESSION['student_id']) {
                redirectStudents('You cannot delete your own account.', 'error');
            }

            $stmt = $conn->prepare(
                "DELETE FROM students WHERE id = :id"
            );

            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                writeAuditLog(
                    $conn,
                    'DELETE_STUDENT',
                    'student',
                    (int)$target['id'],
                    'Deleted student: ' . $target['student_name'] .
                    ' (Student ID: ' . $target['student_id'] . ')'
                );

                redirectStudents('Student deleted successfully.');
            }

            redirectStudents('Student could not be deleted.', 'error');
        }

        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $studentId = strtoupper(trim($_POST['student_id'] ?? ''));
            $name = trim($_POST['student_name'] ?? '');
            $dept = trim($_POST['department'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';

            if (
                !preg_match('/^[A-Z0-9]{4,20}$/', $studentId) ||
                !preg_match('/^[\p{L} .\'-]{2,100}$/u', $name) ||
                $dept === '' ||
                mb_strlen($dept) > 100 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL)
            ) {
                redirectStudents('Please enter valid student details.', 'error');
            }

            if ($id > 0) {
                $existingStmt = $conn->prepare(
                    "SELECT id, student_id, student_name
                     FROM students
                     WHERE id = :id
                     LIMIT 1"
                );

                $existingStmt->execute([':id' => $id]);
                $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

                if (!$existing) {
                    redirectStudents('Student record not found.', 'error');
                }

                $check = $conn->prepare(
                    "SELECT id FROM students
                     WHERE (student_id = :sid OR email = :email)
                     AND id <> :id
                     LIMIT 1"
                );

                $check->execute([
                    ':sid' => $studentId,
                    ':email' => $email,
                    ':id' => $id
                ]);

                if ($check->fetch()) {
                    redirectStudents(
                        'Student ID or email already exists.',
                        'error'
                    );
                }

                if ($password !== '') {
                    if (strlen($password) < 10 || strlen($password) > 72) {
                        redirectStudents(
                            'Password must be 10–72 characters.',
                            'error'
                        );
                    }

                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare(
                        "UPDATE students SET
                         student_id = :sid,
                         student_name = :name,
                         department = :dept,
                         email = :email,
                         password = :password
                         WHERE id = :id"
                    );

                    $stmt->execute([
                        ':sid' => $studentId,
                        ':name' => $name,
                        ':dept' => $dept,
                        ':email' => $email,
                        ':password' => $hash,
                        ':id' => $id
                    ]);
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE students SET
                         student_id = :sid,
                         student_name = :name,
                         department = :dept,
                         email = :email
                         WHERE id = :id"
                    );

                    $stmt->execute([
                        ':sid' => $studentId,
                        ':name' => $name,
                        ':dept' => $dept,
                        ':email' => $email,
                        ':id' => $id
                    ]);
                }

                writeAuditLog(
                    $conn,
                    'UPDATE_STUDENT',
                    'student',
                    $id,
                    'Updated student: ' . $name .
                    ' (Student ID: ' . $studentId .
                    ', Department: ' . $dept . ')'
                );

                redirectStudents('Student updated successfully.');
            } else {
                if (strlen($password) < 10 || strlen($password) > 72) {
                    redirectStudents(
                        'New student password must be 10–72 characters.',
                        'error'
                    );
                }

                $check = $conn->prepare(
                    "SELECT id FROM students
                     WHERE student_id = :sid OR email = :email
                     LIMIT 1"
                );

                $check->execute([
                    ':sid' => $studentId,
                    ':email' => $email
                ]);

                if ($check->fetch()) {
                    redirectStudents(
                        'Student ID or email already exists.',
                        'error'
                    );
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    "INSERT INTO students
                     (student_id, student_name, department, email, password, role)
                     VALUES (:sid, :name, :dept, :email, :password, 'student')"
                );

                $stmt->execute([
                    ':sid' => $studentId,
                    ':name' => $name,
                    ':dept' => $dept,
                    ':email' => $email,
                    ':password' => $hash
                ]);

                $newId = (int)$conn->lastInsertId();

                writeAuditLog(
                    $conn,
                    'ADD_STUDENT',
                    'student',
                    $newId,
                    'Added student: ' . $name .
                    ' (Student ID: ' . $studentId .
                    ', Department: ' . $dept . ')'
                );

                redirectStudents('Student added successfully.');
            }
        }

        redirectStudents('Unknown operation.', 'error');

    } catch (PDOException $e) {
        error_log('Student management database error: ' . $e->getMessage());
        redirectStudents(
            'A database error occurred. Please check the entered details and try again.',
            'error'
        );
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = $conn->prepare(
        "SELECT student_id, student_name, department, email, role
         FROM students
         WHERE (
             student_name LIKE :search1
             OR email LIKE :search2
             OR department LIKE :search3
             OR student_id LIKE :search4
         )
         AND (:dept = '' OR department = :dept)
         ORDER BY `$sort` $order"
    );

    $searchValue = "%$search%";

    $stmt->execute([
        ':search1' => $searchValue,
        ':search2' => $searchValue,
        ':search3' => $searchValue,
        ':search4' => $searchValue,
        ':dept' => $department
    ]);

    $exportRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    writeAuditLog(
        $conn,
        'EXPORT_STUDENTS',
        'student',
        null,
        'Exported student list to CSV. Records exported: ' . count($exportRows)
    );

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="student-list.csv"');

    $output = fopen('php://output', 'w');

    fputcsv(
        $output,
        ['Student ID', 'Name', 'Department', 'Email', 'Role']
    );

    foreach ($exportRows as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

$countStmt = $conn->prepare(
    "SELECT COUNT(*) FROM students
     WHERE (
         student_name LIKE :search1
         OR email LIKE :search2
         OR department LIKE :search3
         OR student_id LIKE :search4
     )
     AND (:dept = '' OR department = :dept)"
);

$searchValue = "%$search%";

$countStmt->execute([
    ':search1' => $searchValue,
    ':search2' => $searchValue,
    ':search3' => $searchValue,
    ':search4' => $searchValue,
    ':dept' => $department
]);

$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$listStmt = $conn->prepare(
    "SELECT id, student_id, student_name, department, email, role
     FROM students
     WHERE (
         student_name LIKE :search1
         OR email LIKE :search2
         OR department LIKE :search3
         OR student_id LIKE :search4
     )
     AND (:dept = '' OR department = :dept)
     ORDER BY `$sort` $order
     LIMIT :limit OFFSET :offset"
);

$listStmt->bindValue(':search1', $searchValue, PDO::PARAM_STR);
$listStmt->bindValue(':search2', $searchValue, PDO::PARAM_STR);
$listStmt->bindValue(':search3', $searchValue, PDO::PARAM_STR);
$listStmt->bindValue(':search4', $searchValue, PDO::PARAM_STR);
$listStmt->bindValue(':dept', $department, PDO::PARAM_STR);
$listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();

$students = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$departments = $conn->query(
    "SELECT DISTINCT department FROM students ORDER BY department"
)->fetchAll(PDO::FETCH_COLUMN);

$editStudent = null;

if (isset($_GET['edit'])) {
    $editStmt = $conn->prepare(
        "SELECT id, student_id, student_name, department, email
         FROM students WHERE id = :id"
    );

    $editStmt->execute([':id' => (int)$_GET['edit']]);
    $editStudent = $editStmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$message = $_SESSION['student_message'] ?? '';
$messageType = $_SESSION['student_message_type'] ?? 'success';

unset($_SESSION['student_message'], $_SESSION['student_message_type']);

function queryLink(array $changes = []): string
{
    return 'students.php?' .
        http_build_query(array_merge($_GET, $changes));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management | Student Hub Portal</title>
    <link rel="stylesheet" href="students.css">
</head>
<body>

<header class="site-header">
    <h1>Student Hub Portal</h1>
    <p>Your One-Stop Learning Platform</p>
</header>

<nav class="navbar">
    <div class="nav-links">
        <a href="admindash.php">Dashboard</a>
        <a href="students.php" class="active">Student Management</a>
        <a href="events.php">Event Management</a>
        <a href="audit_logs.php">Audit Logs</a>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</nav>

<main class="container">

    <div class="heading-row">
        <div>
            <p class="eyebrow">ADMINISTRATOR PANEL</p>
            <h2>Student Management</h2>
        </div>
        <a class="btn" href="#student-form">+ Add Student</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= e($messageType) ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <form class="filters" method="get">
            <input
                type="search"
                name="search"
                placeholder="Search name, email, ID or department..."
                value="<?= e($search) ?>"
            >

            <select name="department">
                <option value="">All departments</option>
                <?php foreach ($departments as $d): ?>
                    <option
                        value="<?= e($d) ?>"
                        <?= $department === $d ? 'selected' : '' ?>
                    >
                        <?= e($d) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="sort">
                <?php foreach ($columns as $column): ?>
                    <option
                        value="<?= e($column) ?>"
                        <?= $sort === $column ? 'selected' : '' ?>
                    >
                        Sort: <?= e(ucwords(str_replace('_', ' ', $column))) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="order">
                <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>
                    Ascending
                </option>
                <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>
                    Descending
                </option>
            </select>

            <button class="btn" type="submit">Apply</button>
            <a class="btn secondary" href="students.php">Reset</a>

            <a class="btn secondary" href="<?= e(queryLink(['export' => 'csv'])) ?>">
                Student List
            </a>
        </form>

        <div class="results-line">
            <strong><?= $total ?></strong> student record(s) found
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Department / Course</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$students): ?>
                        <tr>
                            <td colspan="6" class="empty">No student records found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e($student['student_id']) ?></td>
                            <td><?= e($student['student_name']) ?></td>
                            <td><?= e($student['department']) ?></td>
                            <td><?= e($student['email']) ?></td>
                            <td><span class="role"><?= e($student['role']) ?></span></td>
                            <td class="actions">
                                <a
                                    class="btn small secondary"
                                    href="<?= e(queryLink(['edit' => $student['id']])) ?>#student-form"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    onsubmit="return confirm('Are you sure you want to delete this student?');"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e($_SESSION['csrf_token']) ?>"
                                    >
                                    <input type="hidden" name="action" value="delete">
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$student['id'] ?>"
                                    >
                                    <button class="btn small danger" type="submit">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a class="btn secondary" href="<?= e(queryLink(['page' => $page - 1])) ?>">
                    ← Previous
                </a>
            <?php endif; ?>

            <span>Page <?= $page ?> of <?= $totalPages ?></span>

            <?php if ($page < $totalPages): ?>
                <a class="btn secondary" href="<?= e(queryLink(['page' => $page + 1])) ?>">
                    Next →
                </a>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel form-panel" id="student-form">
        <h2><?= $editStudent ? 'Edit Student' : 'Add New Student' ?></h2>

        <form method="post" class="student-form">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >
            <input type="hidden" name="action" value="save">
            <input
                type="hidden"
                name="id"
                value="<?= (int)($editStudent['id'] ?? 0) ?>"
            >

            <label>
                Student ID
                <input
                    name="student_id"
                    required
                    maxlength="20"
                    value="<?= e($editStudent['student_id'] ?? '') ?>"
                    placeholder="e.g. 25DIT061"
                >
            </label>

            <label>
                Student Name
                <input
                    name="student_name"
                    required
                    maxlength="100"
                    value="<?= e($editStudent['student_name'] ?? '') ?>"
                >
            </label>

            <label>
                Department / Course
                <input
                    name="department"
                    required
                    maxlength="100"
                    value="<?= e($editStudent['department'] ?? '') ?>"
                    placeholder="e.g. CSE, IT, CE"
                >
            </label>

            <label>
                Email
                <input
                    type="email"
                    name="email"
                    required
                    maxlength="150"
                    value="<?= e($editStudent['email'] ?? '') ?>"
                >
            </label>

            <label>
                Password
                <?= $editStudent ? '(leave blank to keep current password)' : '' ?>
                <input
                    type="password"
                    name="password"
                    <?= $editStudent ? '' : 'required' ?>
                    minlength="10"
                    maxlength="72"
                    autocomplete="new-password"
                >
            </label>

            <div class="form-actions">
                <button class="btn" type="submit">
                    <?= $editStudent ? 'Save Changes' : 'Add Student' ?>
                </button>

                <?php if ($editStudent): ?>
                    <a class="btn secondary" href="students.php#student-form">
                        Cancel Edit
                    </a>
                <?php else: ?>
                    <button class="btn secondary" type="reset">Clear Form</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <a class="back-link" href="admindash.php">← Back to Admin Dashboard</a>

</main>

<footer>
    © 2026 Student Hub Portal | Your One-Stop Learning Platform
</footer>

</body>
</html>
