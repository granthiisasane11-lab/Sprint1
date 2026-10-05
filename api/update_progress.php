<?php

header("Content-Type: application/json");

require_once "db.php";


/* =========================
   CHECK REQUEST METHOD
========================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


/* =========================
   GET DATA
========================= */

$user_id = intval($_POST["user_id"] ?? 0);

$goal_id = intval($_POST["goal_id"] ?? 0);

$plan_id = intval($_POST["plan_id"] ?? 0);

$completed_orders_json =
    $_POST["completed_orders"] ?? "[]";


/* =========================
   VALIDATE BASIC DATA
========================= */

if (
    $user_id <= 0 ||
    $goal_id <= 0 ||
    $plan_id <= 0
) {

    echo json_encode([
        "success" => false,
        "message" => "Required progress information is missing."
    ]);

    exit;
}


/* =========================
   DECODE COMPLETED STEPS
========================= */

$completed_orders =
    json_decode(
        $completed_orders_json,
        true
    );


if (!is_array($completed_orders)) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid completed steps data."
    ]);

    exit;
}


/* =========================
   CLEAN STEP ORDERS
========================= */

$cleanOrders = [];


foreach ($completed_orders as $order) {

    $order = intval($order);

    if ($order > 0) {

        $cleanOrders[] = $order;

    }
}


/* Remove duplicate values */

$cleanOrders =
    array_values(
        array_unique(
            $cleanOrders
        )
    );


/* =========================
   VERIFY GOAL
========================= */

$goalStmt = $conn->prepare(
    "SELECT id
     FROM goals
     WHERE id = ?
     AND user_id = ?
     LIMIT 1"
);


$goalStmt->bind_param(
    "ii",
    $goal_id,
    $user_id
);


$goalStmt->execute();


$goalResult =
    $goalStmt->get_result();


if ($goalResult->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Goal does not belong to this user."
    ]);

    $goalStmt->close();
    $conn->close();

    exit;
}


$goalStmt->close();


/* =========================
   VERIFY PLAN
========================= */

$planStmt = $conn->prepare(
    "SELECT id
     FROM plans
     WHERE id = ?
     AND goal_id = ?
     LIMIT 1"
);


$planStmt->bind_param(
    "ii",
    $plan_id,
    $goal_id
);


$planStmt->execute();


$planResult =
    $planStmt->get_result();


if ($planResult->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Plan does not belong to this goal."
    ]);

    $planStmt->close();
    $conn->close();

    exit;
}


$planStmt->close();


/* =========================
   GET ALL PLAN STEPS
========================= */

$stepsStmt = $conn->prepare(
    "SELECT id, step_order
     FROM plan_steps
     WHERE plan_id = ?
     ORDER BY step_order ASC"
);


$stepsStmt->bind_param(
    "i",
    $plan_id
);


$stepsStmt->execute();


$stepsResult =
    $stepsStmt->get_result();


$validOrders = [];


while (
    $row =
    $stepsResult->fetch_assoc()
) {

    $validOrders[] =
        intval(
            $row["step_order"]
        );

}


$stepsStmt->close();


$totalSteps =
    count($validOrders);


/* =========================
   VALIDATE STEP ORDERS
========================= */

$validCompletedOrders = [];


foreach ($cleanOrders as $order) {

    if (
        in_array(
            $order,
            $validOrders,
            true
        )
    ) {

        $validCompletedOrders[] =
            $order;

    }

}


/* =========================
   RESET ALL STEPS
========================= */

$resetStmt = $conn->prepare(
    "UPDATE plan_steps
     SET completed = 0
     WHERE plan_id = ?"
);


$resetStmt->bind_param(
    "i",
    $plan_id
);


if (!$resetStmt->execute()) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to update plan steps."
    ]);

    $resetStmt->close();
    $conn->close();

    exit;
}


$resetStmt->close();


/* =========================
   MARK SELECTED STEPS
========================= */

if (
    count($validCompletedOrders) > 0
) {


    $updateStepStmt = $conn->prepare(
        "UPDATE plan_steps
         SET completed = 1
         WHERE plan_id = ?
         AND step_order = ?"
    );


    foreach (
        $validCompletedOrders
        as $order
    ) {

        $updateStepStmt->bind_param(
            "ii",
            $plan_id,
            $order
        );


        if (!$updateStepStmt->execute()) {

            echo json_encode([
                "success" => false,
                "message" => "Failed to save completed steps."
            ]);

            $updateStepStmt->close();
            $conn->close();

            exit;
        }

    }


    $updateStepStmt->close();

}


/* =========================
   COUNT COMPLETED STEPS
========================= */

$countStmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total_steps,
        SUM(
            CASE
                WHEN completed = 1
                THEN 1
                ELSE 0
            END
        ) AS completed_steps
     FROM plan_steps
     WHERE plan_id = ?"
);


$countStmt->bind_param(
    "i",
    $plan_id
);


$countStmt->execute();


$countResult =
    $countStmt->get_result();


$countData =
    $countResult->fetch_assoc();


$countStmt->close();


$totalSteps =
    intval(
        $countData["total_steps"]
    );


$completedSteps =
    intval(
        $countData["completed_steps"]
    );


/* =========================
   CALCULATE PROGRESS
========================= */

if ($totalSteps > 0) {

    $percentage =
        round(
            (
                $completedSteps /
                $totalSteps
            ) * 100
        );

} else {

    $percentage = 0;

}


$remainingSteps =
    max(
        $totalSteps -
        $completedSteps,
        0
    );


/* =========================
   CHECK EXISTING PROGRESS
========================= */

$checkProgressStmt =
    $conn->prepare(
        "SELECT id
         FROM progress
         WHERE goal_id = ?
         LIMIT 1"
    );


$checkProgressStmt->bind_param(
    "i",
    $goal_id
);


$checkProgressStmt->execute();


$progressResult =
    $checkProgressStmt->get_result();


/* =========================
   UPDATE OR INSERT PROGRESS
========================= */

if (
    $progressResult->num_rows > 0
) {


    $progressRow =
        $progressResult->fetch_assoc();


    $progress_id =
        intval(
            $progressRow["id"]
        );


    $updateProgressStmt =
        $conn->prepare(
            "UPDATE progress
             SET percentage = ?
             WHERE id = ?"
        );


    $updateProgressStmt->bind_param(
        "ii",
        $percentage,
        $progress_id
    );


    $success =
        $updateProgressStmt->execute();


    $updateProgressStmt->close();


} else {


    $insertProgressStmt =
        $conn->prepare(
            "INSERT INTO progress
            (goal_id, percentage)
            VALUES (?, ?)"
        );


    $insertProgressStmt->bind_param(
        "ii",
        $goal_id,
        $percentage
    );


    $success =
        $insertProgressStmt->execute();


    $insertProgressStmt->close();

}


$checkProgressStmt->close();


/* =========================
   RESPONSE
========================= */

if ($success) {

    echo json_encode([

        "success" => true,

        "message" =>
            "Progress saved successfully!",

        "percentage" =>
            $percentage,

        "completed_steps" =>
            $completedSteps,

        "total_steps" =>
            $totalSteps,

        "remaining_steps" =>
            $remainingSteps

    ]);

} else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Failed to save progress."

    ]);

}


$conn->close();

?>