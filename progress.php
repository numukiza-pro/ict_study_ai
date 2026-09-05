<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

require_once "config/database.php";

$user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get courses and quiz progress
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        c.id,
        c.title,
        COUNT(DISTINCT q.id) AS total_quizzes,
        COUNT(DISTINCT CASE
            WHEN qa.id IS NOT NULL THEN q.id
        END) AS completed_quizzes,
        COALESCE(AVG(qa.percentage), 0) AS average_score,
        COALESCE(MAX(qa.percentage), 0) AS best_score
    FROM courses c
    LEFT JOIN quizzes q
        ON c.id = q.course_id
    LEFT JOIN (
        SELECT
            quiz_id,
            MAX(id) AS latest_attempt_id
        FROM quiz_attempts
        WHERE user_id = ?
        GROUP BY quiz_id
    ) latest
        ON q.id = latest.quiz_id
    LEFT JOIN quiz_attempts qa
        ON qa.id = latest.latest_attempt_id
    GROUP BY c.id, c.title
    ORDER BY c.title ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Progress - ICT Study AI</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<nav class="navbar progress-navbar">

    <div class="logo">
        ICT Study AI
    </div>

    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="courses.php">Courses</a>
        <a href="progress.php" class="active" aria-current="page">My Progress</a>
        <a href="ai/chat.php">AI Assistant</a>
        <a href="ai/history.php">History</a>
        <a href="auth/logout.php">Logout</a>
    </div>

</nav>


<main class="progress-page">

    <div class="progress-header">

        <div>
            <p class="eyebrow">YOUR LEARNING DASHBOARD</p>
            <h1>Keep your momentum.</h1>

            <p>
                Track your quiz performance, return to a course, and celebrate each milestone.
            </p>
        </div>

        <a href="courses.php" class="progress-header-button">
            Explore courses <span aria-hidden="true">-&gt;</span>
        </a>

    </div>

    <div class="progress-summary">
        <span><strong><?= $result->num_rows ?></strong> learning paths</span>
        <span>Progress updates after every quiz</span>
    </div>


    <div class="progress-list">

        <?php if ($result->num_rows > 0): ?>

            <?php while ($course = $result->fetch_assoc()): ?>

                <?php

                $total_quizzes = (int)$course["total_quizzes"];
                $completed_quizzes = (int)$course["completed_quizzes"];

                if ($total_quizzes > 0) {
                    $progress = ($completed_quizzes / $total_quizzes) * 100;
                } else {
                    $progress = 0;
                }

                $progress = round($progress);

                $average_score = round((float)$course["average_score"]);

                ?>

                <article class="progress-card">

                    <div class="progress-info">

                        <h2>
                            <?= htmlspecialchars($course["title"]) ?>
                        </h2>

                        <span>
                            <?= $progress ?>%
                        </span>

                    </div>


                    <div class="progress-bar">

                        <div
                            class="progress-fill"
                            style="width: <?= $progress ?>%;">
                        </div>

                    </div>


                    <div class="progress-details">

                        <p>
                            Quizzes completed:
                            <strong>
                                <?= $completed_quizzes ?>
                                /
                                <?= $total_quizzes ?>
                            </strong>
                        </p>

                        <p>
                            Average score:
                            <strong>
                                <?= $average_score ?>%
                            </strong>
                        </p>

                    </div>

                    <div class="progress-actions">
                        <a
                            href="lessons.php?course_id=<?= $course["id"] ?>"
                            class="progress-primary-link"
                        >
                            <?= $progress > 0 ? "Continue course" : "Start course" ?>
                            <span aria-hidden="true">-&gt;</span>
                        </a>

                        <?php if ((float)$course["best_score"] >= 70): ?>
                            <a
                                href="certificate.php?course_id=<?= $course["id"] ?>"
                                class="progress-certificate-link"
                            >
                                View certificate
                            </a>
                        <?php endif; ?>
                    </div>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-progress">

                <h2>No progress yet</h2>

                <p>
                    Start learning and complete quizzes to see your progress.
                </p>

                <a href="courses.php" class="btn">
                    Start Learning
                </a>

            </div>

        <?php endif; ?>

    </div>

</main>

</body>
</html>