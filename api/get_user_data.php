<?php

header("Content-Type: application/json");

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}

$user_id = intval($_GET["user_id"] ?? 0);

if ($user_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid user ID."
    ]);

    exit;
}


/* =========================
   GET USER
========================= */

$userStmt = $conn->prepare(
    "SELECT id, fullname, email, username, created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$userStmt->bind_param("i", $user_id);
$userStmt->execute();

$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);

    $userStmt->close();
    $conn->close();

    exit;
}

$user = $userResult->fetch_assoc();

$userStmt->close();


/* =========================
   GET GOALS
========================= */

$goals = [];

$goalStmt = $conn->prepare(
    "SELECT id, name, description, category,
            start_date, target_date, created_at
     FROM goals
     WHERE user_id = ?
     ORDER BY id DESC"
);

$goalStmt->bind_param("i", $user_id);
$goalStmt->execute();

$goalResult = $goalStmt->get_result();

while ($goal = $goalResult->fetch_assoc()) {

    $goals[] = $goal;
}

$goalStmt->close();


/* =========================
   GET PLANS
========================= */

$plans = [];

$planStmt = $conn->prepare(
    "SELECT
        p.id,
        p.goal_id,
        p.type,
        p.created_at,
        g.name AS goal_name
     FROM plans p
     INNER JOIN goals g
        ON p.goal_id = g.id
     WHERE g.user_id = ?
     ORDER BY p.id DESC"
);

$planStmt->bind_param("i", $user_id);
$planStmt->execute();

$planResult = $planStmt->get_result();

while ($plan = $planResult->fetch_assoc()) {

    $plan_id = intval($plan["id"]);

    $stepStmt = $conn->prepare(
        "SELECT id, step_text, completed, step_order
         FROM plan_steps
         WHERE plan_id = ?
         ORDER BY step_order ASC"
    );

    $stepStmt->bind_param("i", $plan_id);
    $stepStmt->execute();

    $stepResult = $stepStmt->get_result();

    $steps = [];

    while ($step = $stepResult->fetch_assoc()) {

        $steps[] = [
            "id" => intval($step["id"]),
            "text" => $step["step_text"],
            "completed" => intval($step["completed"]),
            "order" => intval($step["step_order"])
        ];
    }

    $stepStmt->close();

    $plan["id"] = intval($plan["id"]);
    $plan["goal_id"] = intval($plan["goal_id"]);
    $plan["steps"] = $steps;

    $plans[] = $plan;
}


/* =========================
   GET PROGRESS
========================= */

$progress = [];

$progressStmt = $conn->prepare(
    "SELECT
        p.id,
        p.goal_id,
        p.percentage,
        p.updated_at
     FROM progress p
     INNER JOIN goals g
        ON p.goal_id = g.id
     WHERE g.user_id = ?
     ORDER BY p.id DESC"
);

$progressStmt->bind_param("i", $user_id);
$progressStmt->execute();

$progressResult = $progressStmt->get_result();

while ($item = $progressResult->fetch_assoc()) {

    $progress[] = [
        "id" => intval($item["id"]),
        "goal_id" => intval($item["goal_id"]),
        "percentage" => intval($item["percentage"]),
        "updated_at" => $item["updated_at"]
    ];
}

$progressStmt->close();


/* =========================
   GET FUTURE CAPSULES
========================= */

$capsules = [];

$capsuleStmt = $conn->prepare(
    "SELECT
        id,
        title,
        message,
        unlock_date,
        created_at
     FROM future_capsules
     WHERE user_id = ?
     ORDER BY id DESC"
);

$capsuleStmt->bind_param("i", $user_id);
$capsuleStmt->execute();

$capsuleResult = $capsuleStmt->get_result();

while ($capsule = $capsuleResult->fetch_assoc()) {

    $capsules[] = [
        "id" => intval($capsule["id"]),
        "title" => $capsule["title"],
        "message" => $capsule["message"],
        "unlock_date" => $capsule["unlock_date"],
        "created_at" => $capsule["created_at"]
    ];
}

$capsuleStmt->close();


/* =========================
   FINAL RESPONSE
========================= */

echo json_encode([
    "success" => true,
    "message" => "User data loaded successfully.",
    "user" => $user,
    "goals" => $goals,
    "plans" => $plans,
    "progress" => $progress,
    "capsules" => $capsules
]);


$conn->close();

?>