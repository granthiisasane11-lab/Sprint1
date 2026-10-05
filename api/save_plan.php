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
$goal_id = intval($_POST["goal_id"] ?? 0);
$type = trim($_POST["type"] ?? "");
$steps_json = $_POST["steps"] ?? "";

if ($user_id <= 0 || $goal_id <= 0 || $type === "" || $steps_json === "") {
    echo json_encode([
        "success" => false,
        "message" => "Required plan information is missing."
    ]);
    exit;
}

if ($type !== "AI" && $type !== "Manual") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid plan type."
    ]);
    exit;
}

$steps = json_decode($steps_json, true);

if (!is_array($steps) || count($steps) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Please add at least one plan step."
    ]);
    exit;
}


/*
   Check that the goal belongs to the logged-in user
*/

$checkGoal = $conn->prepare(
    "SELECT id FROM goals WHERE id = ? AND user_id = ?"
);

$checkGoal->bind_param("ii", $goal_id, $user_id);
$checkGoal->execute();

$goalResult = $checkGoal->get_result();

if ($goalResult->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Goal not found for this user."
    ]);
    exit;
}


/*
   Create the plan
*/

$stmt = $conn->prepare(
    "INSERT INTO plans (goal_id, type)
     VALUES (?, ?)"
);

$stmt->bind_param("is", $goal_id, $type);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to save plan."
    ]);
    exit;
}

$plan_id = $stmt->insert_id;


/*
   Save every plan step
*/

$stepStmt = $conn->prepare(
    "INSERT INTO plan_steps
    (plan_id, step_text, completed, step_order)
    VALUES (?, ?, ?, ?)"
);

foreach ($steps as $index => $step) {

    if (is_array($step)) {
        $stepText = trim($step["text"] ?? "");
        $completed = !empty($step["completed"]) ? 1 : 0;
    } else {
        $stepText = trim($step);
        $completed = 0;
    }

    if ($stepText === "") {
        continue;
    }

    $stepOrder = $index + 1;

    $stepStmt->bind_param(
        "isii",
        $plan_id,
        $stepText,
        $completed,
        $stepOrder
    );

    $stepStmt->execute();
}


echo json_encode([
    "success" => true,
    "message" => "Plan saved successfully!",
    "plan_id" => $plan_id
]);


$stepStmt->close();
$stmt->close();
$checkGoal->close();
$conn->close();

?>