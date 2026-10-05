<?php

header("Content-Type: application/json");

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}

$user_id = intval($_POST["user_id"] ?? 0);
$capsule_id = intval($_POST["capsule_id"] ?? 0);

if ($user_id <= 0 || $capsule_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid user or capsule ID."
    ]);

    exit;
}


/* =========================
   CHECK CAPSULE
========================= */

$checkStmt = $conn->prepare(
    "SELECT id
     FROM future_capsules
     WHERE id = ?
     AND user_id = ?
     LIMIT 1"
);

$checkStmt->bind_param(
    "ii",
    $capsule_id,
    $user_id
);

$checkStmt->execute();

$result = $checkStmt->get_result();

if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Future Capsule not found."
    ]);

    $checkStmt->close();
    $conn->close();

    exit;
}

$checkStmt->close();


/* =========================
   DELETE CAPSULE
========================= */

$deleteStmt = $conn->prepare(
    "DELETE FROM future_capsules
     WHERE id = ?
     AND user_id = ?"
);

$deleteStmt->bind_param(
    "ii",
    $capsule_id,
    $user_id
);

if ($deleteStmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Future Capsule deleted successfully!"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to delete Future Capsule."
    ]);
}

$deleteStmt->close();
$conn->close();

?>