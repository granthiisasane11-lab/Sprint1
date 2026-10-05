<?php

header("Content-Type: application/json");

require_once "db.php";


/* ==========================================
   CHECK REQUEST METHOD
========================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


/* ==========================================
   GET DATA
========================================== */

$user_id = intval($_POST["user_id"] ?? 0);
$title = trim($_POST["title"] ?? "");
$message = trim($_POST["message"] ?? "");
$unlock_date = trim($_POST["unlock_date"] ?? "");


/* ==========================================
   VALIDATE DATA
========================================== */

if (
    $user_id <= 0 ||
    $title === "" ||
    $message === "" ||
    $unlock_date === ""
) {

    echo json_encode([
        "success" => false,
        "message" => "All Future Capsule fields are required."
    ]);

    exit;
}


/* ==========================================
   VALIDATE DATE
========================================== */

$dateObject =
    DateTime::createFromFormat(
        "Y-m-d",
        $unlock_date
    );


if (
    !$dateObject ||
    $dateObject->format("Y-m-d") !== $unlock_date
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid unlock date."
    ]);

    exit;
}


/* ==========================================
   CHECK USER
========================================== */

$userStmt = $conn->prepare(
    "SELECT id
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$userStmt->bind_param(
    "i",
    $user_id
);

$userStmt->execute();

$userResult =
    $userStmt->get_result();


if ($userResult->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);

    $userStmt->close();
    $conn->close();

    exit;
}


$userStmt->close();


/* ==========================================
   SAVE CAPSULE
========================================== */

$stmt = $conn->prepare(
    "INSERT INTO future_capsules
    (user_id, title, message, unlock_date)
    VALUES (?, ?, ?, ?)"
);


$stmt->bind_param(
    "isss",
    $user_id,
    $title,
    $message,
    $unlock_date
);


/* ==========================================
   EXECUTE
========================================== */

if ($stmt->execute()) {

    $capsule_id =
        $stmt->insert_id;


    echo json_encode([
        "success" => true,
        "message" => "Future Capsule saved successfully!",
        "capsule_id" => $capsule_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to save Future Capsule."
    ]);

}


/* ==========================================
   CLOSE
========================================== */

$stmt->close();

$conn->close();

?>