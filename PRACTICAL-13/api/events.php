
<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function respond($success, $message, $data = null, $status = 200) {
    http_response_code($status);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

if (
    empty($_SESSION["student_id"]) ||
    strtolower($_SESSION["role"] ?? "") !== "admin"
) {
    respond(false, "Admin login required.", null, 401);
}

$method = $_SERVER["REQUEST_METHOD"];

try {
    if ($method === "GET") {
        $stmt = $conn->query(
            "SELECT id, title, category, event_date, organizer,
                    venue, event_time, status, poster
             FROM events
             ORDER BY event_date ASC, id DESC"
        );

        respond(true, "Events fetched successfully.", $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        $input = $_POST;
    }

    if ($method === "POST") {
        $title = trim($input["title"] ?? "");
        $category = trim($input["category"] ?? "");
        $event_date = trim($input["event_date"] ?? "");
        $organizer = trim($input["organizer"] ?? "");
        $venue = trim($input["venue"] ?? "");
        $event_time = trim($input["event_time"] ?? "");
        $status = trim($input["status"] ?? "");
        $poster = trim($input["poster"] ?? "");

        if (
            $title === "" || $category === "" || $event_date === "" ||
            $organizer === "" || $venue === "" || $event_time === "" ||
            $status === ""
        ) {
            respond(false, "Please fill all required fields.", null, 422);
        }

        $stmt = $conn->prepare(
            "INSERT INTO events
             (title, category, event_date, organizer, venue, event_time, status, poster)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $title, $category, $event_date, $organizer,
            $venue, $event_time, $status, $poster !== "" ? $poster : null
        ]);

        $id = (int)$conn->lastInsertId();

        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);

        respond(true, "Event added successfully.", $stmt->fetch(PDO::FETCH_ASSOC), 201);
    }

    if ($method === "PUT") {
        $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            respond(false, "A valid event ID is required.", null, 422);
        }

        $stmt = $conn->prepare("SELECT id FROM events WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            respond(false, "Event not found.", null, 404);
        }

        $title = trim($input["title"] ?? "");
        $category = trim($input["category"] ?? "");
        $event_date = trim($input["event_date"] ?? "");
        $organizer = trim($input["organizer"] ?? "");
        $venue = trim($input["venue"] ?? "");
        $event_time = trim($input["event_time"] ?? "");
        $status = trim($input["status"] ?? "");
        $poster = trim($input["poster"] ?? "");

        if (
            $title === "" || $category === "" || $event_date === "" ||
            $organizer === "" || $venue === "" || $event_time === "" ||
            $status === ""
        ) {
            respond(false, "Please fill all required fields.", null, 422);
        }

        $stmt = $conn->prepare(
            "UPDATE events
             SET title = ?, category = ?, event_date = ?, organizer = ?,
                 venue = ?, event_time = ?, status = ?, poster = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $title, $category, $event_date, $organizer,
            $venue, $event_time, $status, $poster !== "" ? $poster : null, $id
        ]);

        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);

        respond(true, "Event updated successfully.", $stmt->fetch(PDO::FETCH_ASSOC));
    }

    if ($method === "DELETE") {
        $id = filter_var(
            $input["id"] ?? $_GET["id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            respond(false, "A valid event ID is required.", null, 422);
        }

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            respond(false, "Event not found.", null, 404);
        }

        respond(true, "Event deleted successfully.", ["id" => $id]);
    }

    header("Allow: GET, POST, PUT, DELETE");
    respond(false, "Method not allowed.", null, 405);

} catch (PDOException $e) {
    error_log("Events API database error: " . $e->getMessage());
    respond(false, "A database error occurred.", null, 500);
} catch (Throwable $e) {
    error_log("Events API error: " . $e->getMessage());
    respond(false, "An unexpected server error occurred.", null, 500);
}
