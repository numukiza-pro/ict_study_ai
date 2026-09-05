<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Dashboard statistics
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            COUNT(*) AS total_attempts,
            COUNT(DISTINCT quiz_id) AS total_quizzes,
            COALESCE(AVG(percentage), 0) AS average_score,
            COALESCE(MAX(percentage), 0) AS best_score
        FROM quiz_attempts
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$stats = $result->fetch_assoc();

$total_attempts = $stats['total_attempts'];
$total_quizzes = $stats['total_quizzes'];
$average_score = round($stats['average_score'], 2);
$best_score = round($stats['best_score'], 2);


/*
|--------------------------------------------------------------------------
| Recent attempts
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            qa.quiz_id,
            qa.score,
            qa.total_questions,
            qa.percentage,
            qa.completed_at,
            q.title
        FROM quiz_attempts qa
        LEFT JOIN quizzes q
            ON qa.quiz_id = q.id
        WHERE qa.user_id = ?
        ORDER BY qa.completed_at DESC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$attempts = $stmt->get_result();

?>

<?php

/*
|--------------------------------------------------------------------------
| Progress per quiz
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            qa.quiz_id,
            q.title,
            COUNT(qa.id) AS attempts,
            MAX(qa.percentage) AS best_score,
            AVG(qa.percentage) AS average_score
        FROM quiz_attempts qa
        LEFT JOIN quizzes q
            ON qa.quiz_id = q.id
        WHERE qa.user_id = ?
        GROUP BY qa.quiz_id, q.title
        ORDER BY best_score DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$quiz_progress = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Progress</title>

    <link rel="stylesheet"
          href="style.css">

</head>

<body>

<div class="dashboard-container">

    <h1>My Progress</h1>

    <p class="dashboard-subtitle">
        Track your quiz performance
    </p>


    <!-- Statistics -->

    <div class="stats-container">

        <div class="stat-card">

            <h3>Total Attempts</h3>

            <p>
                <?= $total_attempts ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>Total Quizzes</h3>

            <p>
                <?= $total_quizzes ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>Average Score</h3>

            <p>
                <?= $average_score ?>%
            </p>

        </div>


        <div class="stat-card">

            <h3>Best Score</h3>

            <p>
                <?= $best_score ?>%
            </p>

        </div>

    </div>


    <!-- Recent Attempts -->

    <div class="attempts-card">

        <h2>Recent Quiz Attempts</h2>

        <table>

            <thead>

                <tr>

                    <th>Quiz</th>

                    <th>Score</th>

                    <th>Total</th>

                    <th>Percentage</th>

                    <th>Date</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($attempts->num_rows > 0): ?>

                <?php while ($attempt = $attempts->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $attempt['title'] ?? 'Quiz'
                            ) ?>
                        </td>

                        <td>
                            <?= $attempt['score'] ?>
                        </td>

                        <td>
                            <?= $attempt['total_questions'] ?>
                        </td>

                        <td>

                            <span class="percentage">

                                <?= $attempt['percentage'] ?>%

                            </span>

                        </td>

                        <td>
                            <?= $attempt['completed_at'] ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="5"
                        class="no-data">

                        You have not completed any quiz yet.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <div class="quiz-progress-card">

    <h2>Quiz Progress</h2>

    <p class="section-description">
        Your performance in each quiz
    </p>

    <?php if ($quiz_progress->num_rows > 0): ?>

        <?php while ($quiz = $quiz_progress->fetch_assoc()): ?>

            <?php
                $best_score = round(
                    (float)$quiz['best_score'],
                    2
                );

                $average_score = round(
                    (float)$quiz['average_score'],
                    2
                );
            ?>

            <div class="quiz-progress">

                <div class="quiz-progress-header">

                    <h3>
                        <?= htmlspecialchars(
                            $quiz['title'] ?? 'Quiz'
                        ) ?>
                    </h3>

                    <span>
                        <?= $best_score ?>%
                    </span>

                </div>

                <div class="progress-bar">

                    <div
                        class="progress-fill"
                        style="width: <?= $best_score ?>%;"
                    ></div>

                </div>

                <div class="quiz-info">

                    <span>
                        Attempts:
                        <?= $quiz['attempts'] ?>
                    </span>

                    <span>
                        Average:
                        <?= $average_score ?>%
                    </span>

                    <span>
                        Best:
                        <?= $best_score ?>%
                    </span>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p class="no-progress">
            No quiz progress available yet.
        </p>

    <?php endif; ?>

</div>

</div>


</body>

</html>