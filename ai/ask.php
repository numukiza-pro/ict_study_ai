<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "You must login first."
    ]);

    exit();
}

require_once "../config/database.php";
require_once "../config/ai.php";


$input = json_decode(
    file_get_contents("php://input"),
    true
);


if (!isset($input["message"]) || trim($input["message"]) === "") {

    echo json_encode([
        "success" => false,
        "message" => "Message is required."
    ]);

    exit();
}


$userId = $_SESSION["user_id"];

$userMessage = trim($input["message"]);

$tableCheck = $conn->query("SHOW TABLES LIKE 'chat_history'");
$chatHistoryAvailable = $tableCheck->num_rows > 0;


$systemMessage = "
You are ICT Study AI, an intelligent ICT teacher.

Help students learn:

- HTML
- CSS
- JavaScript
- PHP
- React.js
- Node.js
- Python
- MySQL
- MongoDB
- Networking
- Cybersecurity
- Software Development

Explain concepts clearly and simply.

When appropriate:

1. Explain the concept.
2. Give a practical example.
3. Show code.
4. Give the student a small exercise.

Do not make the answer unnecessarily complicated.
";

$messages = [
    [
        "role" => "system",
        "content" => $systemMessage
    ]
];


/*
|--------------------------------------------------------------------------
| Get previous conversations
|--------------------------------------------------------------------------
*/

if ($chatHistoryAvailable) {

$historySql = "
    SELECT user_message, ai_response
    FROM chat_history
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 10
";

$historyStmt = $conn->prepare($historySql);

$historyStmt->bind_param("i", $userId);

$historyStmt->execute();

$historyResult = $historyStmt->get_result();

$history = [];

while ($row = $historyResult->fetch_assoc()) {
    $history[] = $row;
}

$historyStmt->close();

} else {
    $history = [];
}


/*
|--------------------------------------------------------------------------
| Add previous conversations to AI context
|--------------------------------------------------------------------------
*/

$history = array_reverse($history);

foreach ($history as $chat) {

    $messages[] = [
        "role" => "user",
        "content" => $chat["user_message"]
    ];

    $messages[] = [
        "role" => "assistant",
        "content" => $chat["ai_response"]
    ];
}


/*
|--------------------------------------------------------------------------
| Add current question
|--------------------------------------------------------------------------
*/

$messages[] = [
    "role" => "user",
    "content" => $userMessage
];


$data = [
    "model" => $aiModel,
    "messages" => $messages
];


$ch = curl_init(
    "https://api.openai.com/v1/chat/completions"
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
        "Authorization: Bearer " . $apiKey
    ]
);


curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode($data)
);


$response = curl_exec($ch);


$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


curl_close($ch);


if ($response === false) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to connect to AI service."
    ]);

    exit();
}


$result = json_decode(
    $response,
    true
);


if ($httpCode < 200 || $httpCode >= 300) {

    echo json_encode([
        "success" => false,
        "message" => "AI API Error",
        "http_code" => $httpCode,
        "details" => $result
    ]);

    exit();
}


$answer =
    $result["choices"][0]["message"]["content"]
    ?? "No response received.";


/*
|--------------------------------------------------------------------------
| Save conversation
|--------------------------------------------------------------------------
*/

if ($chatHistoryAvailable) {

$sql = "
    INSERT INTO chat_history
    (user_id, user_message, ai_response)
    VALUES (?, ?, ?)
";


$stmt = $conn->prepare($sql);


$stmt->bind_param(
    "iss",
    $userId,
    $userMessage,
    $answer
);


$stmt->execute();


$stmt->close();

}


echo json_encode([

    "success" => true,

    "answer" => $answer

]);

?>