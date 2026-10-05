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
$fullname = trim($_POST["fullname"] ?? "");

if ($user_id <= 0 || $fullname === "") {

    echo json_encode([
        "success" => false,
        "message" => "User ID and full name are required."
    ]);

    exit;
}


/* =========================
   CHECK USER
========================= */

$checkStmt = $conn->prepare(
    "SELECT id
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$checkStmt->bind_param(
    "i",
    $user_id
);

$checkStmt->execute();

$result = $checkStmt->get_result();

if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);

    $checkStmt->close();
    $conn->close();

    exit;
}

$checkStmt->close();


/* =========================
   UPDATE FULL NAME
========================= */

$updateStmt = $conn->prepare(
    "UPDATE users
     SET fullname = ?
     WHERE id = ?"
);

$updateStmt->bind_param(
    "si",
    $fullname,
    $user_id
);

if ($updateStmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Profile updated successfully!",
        "fullname" => $fullname
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to update profile."
    ]);
}

$updateStmt->close();
$conn->close();

?>