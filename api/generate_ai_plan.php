<?php

header("Content-Type: application/json");

require_once "db.php";
require_once "config.php";


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

$goal = trim($_POST["goal"] ?? "");

$description = trim($_POST["description"] ?? "");

$category = trim($_POST["category"] ?? "");

$start_date = trim($_POST["start_date"] ?? "");

$target_date = trim($_POST["target_date"] ?? "");


/* =========================
   VALIDATION
========================= */

if ($user_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid user."
    ]);

    exit;
}


if ($goal === "") {

    echo json_encode([
        "success" => false,
        "message" => "Goal is required."
    ]);

    exit;
}


if ($target_date === "") {

    echo json_encode([
        "success" => false,
        "message" => "Target date is required."
    ]);

    exit;
}


/* =========================
   CHECK API KEY
========================= */

if (
    !defined("OPENAI_API_KEY") ||
    OPENAI_API_KEY === "" ||
    OPENAI_API_KEY === "PASTE_YOUR_OPENAI_API_KEY_HERE"
) {

    echo json_encode([
        "success" => false,
        "message" => "OpenAI API key is not configured."
    ]);

    exit;
}


/* =========================
   VERIFY USER
========================= */

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


$userStmt->close();


/* =========================
   CREATE AI PROMPT
========================= */

$prompt = "

Create a personalized step-by-step plan for the following goal.

Goal:
{$goal}

Description:
{$description}

Category:
{$category}

Start Date:
{$start_date}

Target Date:
{$target_date}


IMPORTANT INSTRUCTIONS:

1. The plan must be specifically about the user's goal.
2. Do NOT give generic study steps unless the goal is actually about studying.
3. Every step must be directly related to the goal.
4. Consider the category, description, start date and target date.
5. Create between 8 and 15 practical steps.
6. Steps should be realistic and achievable.
7. Start with beginner-friendly steps and gradually increase difficulty.
8. The steps should lead the user toward completing the goal.
9. Do not repeat the same step.
10. Use simple, clear English.
11. Return ONLY the requested JSON structure.


For example, if the goal is learning a dance:

Do NOT return:
- Understand the complete syllabus
- Create a study schedule
- Learn basic concepts

Instead, create dance-specific steps such as:
- Learn the basic posture and hand movements
- Practice the basic footwork and rhythm
- Learn beginner dance expressions
- Practice a short sequence
- Record yourself and review your movements

The same principle applies to every type of goal.
";


/* =========================
   JSON SCHEMA
========================= */

$schema = [

    "type" => "object",

    "properties" => [

        "steps" => [

            "type" => "array",

            "items" => [

                "type" => "object",

                "properties" => [

                    "step_number" => [
                        "type" => "integer"
                    ],

                    "step" => [
                        "type" => "string"
                    ]

                ],

                "required" => [
                    "step_number",
                    "step"
                ],

                "additionalProperties" => false

            ]

        ]

    ],

    "required" => [
        "steps"
    ],

    "additionalProperties" => false
];


/* =========================
   OPENAI REQUEST
========================= */

$requestBody = [

    "model" => "gpt-6-luna",

    "input" => [

        [

            "role" => "system",

            "content" => [

                [

                    "type" => "input_text",

                    "text" =>
                        "You create highly personalized goal plans. " .
                        "Always create steps that are specifically related " .
                        "to the user's goal. Never use generic template steps."

                ]

            ]

        ],

        [

            "role" => "user",

            "content" => [

                [

                    "type" => "input_text",

                    "text" => $prompt

                ]

            ]

        ]

    ],

    "text" => [

        "format" => [

            "type" => "json_schema",

            "name" => "futureme_plan",

            "strict" => true,

            "schema" => $schema

        ]

    ],

    "max_output_tokens" => 2000

];


/* =========================
   CURL REQUEST
========================= */

$ch = curl_init(
    "https://api.openai.com/v1/responses"
);


curl_setopt(
    $ch,
    CURLOPT_RETURNTRANSFER,
    true
);


curl_setopt(
    $ch,
    CURLOPT_POST,
    true
);


curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    [

        "Content-Type: application/json",

        "Authorization: Bearer " .
        OPENAI_API_KEY

    ]
);


curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode($requestBody)
);


$response = curl_exec($ch);


$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$curlError =
    curl_error($ch);


curl_close($ch);


/* =========================
   CURL ERROR
========================= */

if ($response === false || $curlError !== "") {

    echo json_encode([

        "success" => false,

        "message" =>
            "Unable to connect to OpenAI.",

        "error" =>
            $curlError

    ]);

    $conn->close();

    exit;
}


/* =========================
   DECODE OPENAI RESPONSE
========================= */

$responseData =
    json_decode(
        $response,
        true
    );


/* =========================
   OPENAI API ERROR
========================= */

if ($httpCode < 200 || $httpCode >= 300) {

    $errorMessage =
        "OpenAI API request failed.";

    if (
        isset(
            $responseData["error"]["message"]
        )
    ) {

        $errorMessage =
            $responseData["error"]["message"];

    }


    echo json_encode([

        "success" => false,

        "message" =>
            $errorMessage,

        "http_code" =>
            $httpCode

    ]);

    $conn->close();

    exit;
}


/* =========================
   GET OUTPUT TEXT
========================= */

$outputText = "";


/*
 * Responses API normally returns
 * output items containing message content.
 */

if (
    isset($responseData["output"]) &&
    is_array($responseData["output"])
) {

    foreach (
        $responseData["output"]
        as $outputItem
    ) {

        if (
            isset($outputItem["content"]) &&
            is_array($outputItem["content"])
        ) {

            foreach (
                $outputItem["content"]
                as $contentItem
            ) {

                if (
                    isset($contentItem["text"])
                ) {

                    $outputText .=
                        $contentItem["text"];

                }

            }

        }

    }

}


/* =========================
   FALLBACK RESPONSE TEXT
========================= */

if (
    $outputText === "" &&
    isset($responseData["output_text"])
) {

    $outputText =
        $responseData["output_text"];

}


if ($outputText === "") {

    echo json_encode([

        "success" => false,

        "message" =>
            "OpenAI returned an empty response.",

        "debug" =>
            $responseData

    ]);

    $conn->close();

    exit;
}


/* =========================
   DECODE AI JSON
========================= */

$aiData =
    json_decode(
        $outputText,
        true
    );


if (
    !is_array($aiData) ||
    !isset($aiData["steps"]) ||
    !is_array($aiData["steps"])
) {

    echo json_encode([

        "success" => false,

        "message" =>
            "AI returned an invalid plan format.",

        "raw_response" =>
            $outputText

    ]);

    $conn->close();

    exit;
}


/* =========================
   CLEAN STEPS
========================= */

$steps = [];


foreach (
    $aiData["steps"]
    as $index => $item
) {

    if (
        !isset($item["step"]) ||
        trim($item["step"]) === ""
    ) {

        continue;

    }


    $steps[] = [

        "step_number" =>
            count($steps) + 1,

        "step" =>
            trim($item["step"])

    ];

}


/* =========================
   VALIDATE STEP COUNT
========================= */

if (count($steps) < 1) {

    echo json_encode([

        "success" => false,

        "message" =>
            "AI did not generate any usable steps."

    ]);

    $conn->close();

    exit;
}


/* =========================
   LIMIT STEPS
========================= */

$steps =
    array_slice(
        $steps,
        0,
        15
    );


/* =========================
   SUCCESS RESPONSE
========================= */

echo json_encode([

    "success" => true,

    "message" =>
        "AI plan generated successfully!",

    "steps" =>
        $steps

]);


$conn->close();

?>