


<?php

function writeAuditLog(
    PDO $conn,
    string $action,
    string $entityType,
    ?int $entityId = null,
    ?string $details = null
): bool {
    try {
        $userId = null;

        if (!empty($_SESSION['student_id'])) {
            $studentId = (string) $_SESSION['student_id'];

            $stmt = $conn->prepare(
                "SELECT id FROM students WHERE student_id = ? LIMIT 1"
            );
            $stmt->execute([$studentId]);

            $userId = $stmt->fetchColumn();

            if ($userId === false) {
                $userId = null;
            }
        }

        $userName = $_SESSION['student_name'] ?? 'Unknown User';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        $stmt = $conn->prepare(
            "INSERT INTO audit_logs
            (user_id, user_name, action, entity_type, entity_id, details, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $userId,
            $userName,
            $action,
            $entityType,
            $entityId,
            $details,
            $ipAddress
        ]);

        return true;
    } catch (Throwable $e) {
        error_log('Audit logging failed: ' . $e->getMessage());
        return false;
    }
}
