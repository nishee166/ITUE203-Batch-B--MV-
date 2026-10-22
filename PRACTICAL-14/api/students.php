<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../database.php";

check_session_timeout();

function response($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (
    empty($_SESSION["student_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    response(401, [
        "success" => false,
        "message" => "Admin login required."
    ]);
}

$method = $_SERVER["REQUEST_METHOD"];

try {
    if ($method === "GET") {
        $stmt = $conn->query(
            "SELECT id, student_id, student_name, department,
                    email, role
             FROM students
             ORDER BY id DESC"
        );

        response(200, [
            "success" => true,
            "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    $csrf = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

    if (
        empty($_SESSION["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $csrf)
    ) {
        response(403, [
            "success" => false,
            "message" => "Invalid CSRF token."
        ]);
    }

    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        response(400, [
            "success" => false,
            "message" => "Invalid JSON data."
        ]);
    }

    if ($method === "POST") {
        $studentId = strtoupper(trim($input["student_id"] ?? ""));
        $name = trim($input["student_name"] ?? "");
        $department = trim($input["department"] ?? "");
        $email = trim($input["email"] ?? "");
        $password = $input["password"] ?? "";

        if (
            !preg_match('/^[A-Z0-9]{4,20}$/', $studentId) ||
            !preg_match("/^[\\p{L} .'-]{2,100}$/u", $name) ||
            $department === "" ||
            strlen($department) > 100 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !is_string($password) ||
            strlen($password) < 10 ||
            strlen($password) > 72
        ) {
            response(422, [
                "success" => false,
                "message" => "Please enter valid details. Password must be 10–72 characters."
            ]);
        }

        $check = $conn->prepare(
            "SELECT id FROM students
             WHERE student_id = ? OR email = ?"
        );
        $check->execute([$studentId, $email]);

        if ($check->fetch()) {
            response(409, [
                "success" => false,
                "message" => "Student ID or email already exists."
            ]);
        }

        $stmt = $conn->prepare(
            "INSERT INTO students
             (student_id, student_name, department, email, password, role)
             VALUES (?, ?, ?, ?, ?, 'student')"
        );

        $stmt->execute([
            $studentId,
            $name,
            $department,
            $email,
            password_hash($password, PASSWORD_DEFAULT)
        ]);

        response(201, [
            "success" => true,
            "message" => "Student added successfully.",
            "id" => $conn->lastInsertId()
        ]);
    }

   

       if ($method === "PUT") {
    $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);
    $studentId = strtoupper(trim($input["student_id"] ?? ""));
    $name = trim($input["student_name"] ?? "");
    $department = trim($input["department"] ?? "");
    $email = strtolower(trim($input["email"] ?? ""));
    $password = $input["password"] ?? "";

    if (
        !$id ||
        !preg_match('/^[A-Z0-9]{4,20}$/', $studentId) ||
        !preg_match("/^[\\p{L} .'-]{2,100}$/u", $name) ||
        $department === "" ||
        strlen($department) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !is_string($password) ||
        ($password !== "" && (strlen($password) < 10 || strlen($password) > 72))
    ) {
        response(422, [
            "success" => false,
            "message" => "Please enter valid details."
        ]);
    }

    $check = $conn->prepare(
        "SELECT id FROM students
         WHERE (student_id = ? OR email = ?) AND id <> ?"
    );
    $check->execute([$studentId, $email, $id]);

    if ($check->fetch()) {
        response(409, [
            "success" => false,
            "message" => "Student ID or email already exists."
        ]);
    }

    $check = $conn->prepare("SELECT id FROM students WHERE id = ?");
    $check->execute([$id]);

    if (!$check->fetch()) {
        response(404, [
            "success" => false,
            "message" => "Student not found."
        ]);
    }

    if ($password !== "") {
        $stmt = $conn->prepare(
            "UPDATE students
             SET student_id = ?, student_name = ?, department = ?,
                 email = ?, password = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $studentId,
            $name,
            $department,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $id
        ]);
    } else {
        $stmt = $conn->prepare(
            "UPDATE students
             SET student_id = ?, student_name = ?, department = ?, email = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $studentId,
            $name,
            $department,
            $email,
            $id
        ]);
    }

    response(200, [
        "success" => true,
        "message" => "Student updated successfully."
    ]);
}
     

    if ($method === "DELETE") {
        $id = filter_var(
            $input["id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id) {
            response(422, [
                "success" => false,
                "message" => "Valid student ID is required."
            ]);
        }

        $check = $conn->prepare(
            "SELECT student_id, role FROM students WHERE id = ?"
        );
        $check->execute([$id]);
        $student = $check->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            response(404, [
                "success" => false,
                "message" => "Student not found."
            ]);
        }

        if (
            $student["student_id"] === $_SESSION["student_id"] ||
            $student["role"] === "admin"
        ) {
            response(403, [
                "success" => false,
                "message" => "Admin accounts cannot be deleted here."
            ]);
        }

        $stmt = $conn->prepare(
            "DELETE FROM students WHERE id = ?"
        );
        $stmt->execute([$id]);

        response(200, [
            "success" => true,
            "message" => "Student deleted successfully."
        ]);
    }

    header("Allow: GET, POST, PUT, DELETE");

    response(405, [
        "success" => false,
        "message" => "Method not allowed."
    ]);

} catch (PDOException $e) {
    if ($e->getCode() === "23000") {
        response(409, [
            "success" => false,
            "message" => "Duplicate details or a related database record prevents this operation."
        ]);
    }

    response(500, [
        "success" => false,
        "message" => "A database error occurred."
    ]);
}
?>