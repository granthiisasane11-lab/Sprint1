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

$login = trim($_POST["login"] ?? "");
$password = $_POST["password"] ?? "";

if ($login === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please enter username/email and password."
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, fullname, email, username, password
     FROM users
     WHERE username = ? OR email = ?
     LIMIT 1"
);

$stmt->bind_param("ss", $login, $login);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password"])) {
    echo json_encode([
        "success" => false,
        "message" => "Incorrect password."
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Login successful!",
    "user" => [
        "id" => $user["id"],
        "fullname" => $user["fullname"],
        "email" => $user["email"],
        "username" => $user["username"]
    ]
]);

$stmt->close();
$conn->close();

?>