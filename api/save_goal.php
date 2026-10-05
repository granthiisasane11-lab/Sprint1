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
$name = trim($_POST["name"] ?? "");
$description = trim($_POST["description"] ?? "");
$category = trim($_POST["category"] ?? "");
$start_date = $_POST["start_date"] ?? "";
$target_date = $_POST["target_date"] ?? "";

if (
    $user_id <= 0 ||
    $name === "" ||
    $category === "" ||
    $start_date === "" ||
    $target_date === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Please fill all required goal fields."
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO goals
    (user_id, name, description, category, start_date, target_date)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "isssss",
    $user_id,
    $name,
    $description,
    $category,
    $start_date,
    $target_date
);

if ($stmt->execute()) {

    $goal_id = $stmt->insert_id;

    echo json_encode([
        "success" => true,
        "message" => "Goal saved successfully!",
        "goal_id" => $goal_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to save goal."
    ]);

}

$stmt->close();
$conn->close();

?>