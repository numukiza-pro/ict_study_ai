<?php

session_start();

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| Check login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$userCheck = $conn->prepare(
    "SELECT id FROM users WHERE id = ?"
);

$userCheck->bind_param("i", $user_id);
$userCheck->execute();
$userExists = $userCheck->get_result()->num_rows === 1;
$userCheck->close();

if (!$userExists) {
    session_unset();
    session_destroy();
    header("Location: auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get quiz ID
|--------------------------------------------------------------------------
*/

$quiz_id = $_POST['quiz_id'] ?? null;

if (!$quiz_id) {
    header("Location: dashboard.php");
    exit;
}

$quiz_id = (int)$quiz_id;

/*
|--------------------------------------------------------------------------
| Get submitted answers
|--------------------------------------------------------------------------
*/

$answers = $_POST['answers'] ?? [];

if (!is_array($answers)) {
    header("Location: dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get correct answers from database
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, correct_answer
        FROM questions
        WHERE quiz_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: dashboard.php");
    exit;
}

$stmt->bind_param("i", $quiz_id);
$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Calculate score
|--------------------------------------------------------------------------
*/

$score = 0;
$total_questions = 0;

while ($question = $result->fetch_assoc()) {

    $total_questions++;

    $question_id = $question['id'];

    $correct_answer = trim(
        strtolower($question['correct_answer'])
    );

    $user_answer = "";

    if (isset($answers[$question_id])) {
        $user_answer = trim(
            strtolower($answers[$question_id])
        );
    }

    if ($user_answer === $correct_answer) {
        $score++;
    }
}

/*
|--------------------------------------------------------------------------
| Calculate percentage
|--------------------------------------------------------------------------
*/

$percentage = 0;

if ($total_questions > 0) {
    $percentage = ($score / $total_questions) * 100;
}

/*
|--------------------------------------------------------------------------
| Save quiz attempt
|--------------------------------------------------------------------------
*/

$insert = "INSERT INTO quiz_attempts
           (
               user_id,
               quiz_id,
               score,
               total_questions,
               percentage
           )
           VALUES (?, ?, ?, ?, ?)";

$attempt_stmt = $conn->prepare($insert);

if (!$attempt_stmt) {
    header("Location: dashboard.php");
    exit;
}

$attempt_stmt->bind_param(
    "iiiid",
    $user_id,
    $quiz_id,
    $score,
    $total_questions,
    $percentage
);

if (!$attempt_stmt->execute()) {
    header("Location: dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get attempt ID
|--------------------------------------------------------------------------
*/

$attempt_id = $conn->insert_id;

/*
|--------------------------------------------------------------------------
| Return result
|--------------------------------------------------------------------------
*/

$_SESSION["quiz_score"] = $score;
$_SESSION["quiz_total"] = $total_questions;
$_SESSION["quiz_percentage"] = round($percentage, 2);

header("Location: quiz_result.php");
exit;

?>