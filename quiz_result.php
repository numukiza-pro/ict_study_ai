<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}


if (
    !isset($_SESSION["quiz_score"]) ||
    !isset($_SESSION["quiz_total"]) ||
    !isset($_SESSION["quiz_percentage"])
) {
    header("Location: dashboard.php");
    exit();
}


$score = $_SESSION["quiz_score"];

$total = $_SESSION["quiz_total"];

$percentage =
    $_SESSION["quiz_percentage"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Quiz Result</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<div class="quiz-result">

    <h1>Quiz Completed</h1>


    <div class="result-card">

        <h2>Your Score</h2>

        <div class="score">

            <?= $score ?>

            /

            <?= $total ?>

        </div>


        <p class="percentage">

            <?= $percentage ?>%

        </p>


        <?php if ($percentage >= 70): ?>

            <p>
                Excellent! You passed the quiz.
            </p>

        <?php elseif ($percentage >= 50): ?>

            <p>
                Good job! Keep practicing.
            </p>

        <?php else: ?>

            <p>
                Keep studying and try again.
            </p>

        <?php endif; ?>


        <div>

            <a
                href="courses.php"
                class="course-button"
            >
                Back to Courses
            </a>


            <a
                href="dashboard.php"
                class="course-button"
            >
                Dashboard
            </a>

        </div>

    </div>

</div>

</body>

</html>