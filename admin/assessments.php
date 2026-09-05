<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../config/database.php";

$quizSql = "
    SELECT
        q.id,
        q.title,
        q.description,
        c.title AS course_title,
        COUNT(DISTINCT questions.id) AS question_count,
        COUNT(DISTINCT qa.id) AS attempt_count
    FROM quizzes q
    INNER JOIN courses c ON c.id = q.course_id
    LEFT JOIN questions ON questions.quiz_id = q.id
    LEFT JOIN quiz_attempts qa ON qa.quiz_id = q.id
    GROUP BY q.id, q.title, q.description, c.title
    ORDER BY q.id DESC
";
$quizResult = $conn->query($quizSql);

$exerciseSql = "
    SELECT e.title, e.instructions, c.title AS course_title
    FROM course_exercises e
    INNER JOIN courses c ON c.id = e.course_id
    ORDER BY e.id DESC
";
$exerciseResult = $conn->query($exerciseSql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assessments</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar admin-navbar">
    <div class="logo">ICT Study AI Admin</div>
    <div class="nav-links">
        <a href="index.php">Admin Dashboard</a>
        <a href="courses.php">Courses</a>
        <a href="lessons.php">Lessons</a>
        <a href="assessments.php" class="active" aria-current="page">Assessments</a>
        <a href="users.php">Users</a>
        <a href="../auth/logout.php">Logout</a>
    </div>
</nav>

<main class="admin-assessments-page">
    <section class="admin-assessments-header">
        <p class="eyebrow">ASSESSMENT CONTROL</p>
        <h1>Review every checkpoint.</h1>
        <p>See quizzes, question totals, attempts, and the practice exercises attached to each course.</p>
    </section>

    <section class="admin-assessment-section">
        <div class="admin-section-title">
            <div>
                <p class="eyebrow">QUIZZES</p>
                <h2><?= $quizResult->num_rows ?> assessments</h2>
            </div>
        </div>

        <div class="admin-assessment-grid">
            <?php while ($quiz = $quizResult->fetch_assoc()): ?>
                <article class="admin-assessment-card">
                    <span class="admin-card-index">QUIZ <?= (int)$quiz["id"] ?></span>
                    <h3><?= htmlspecialchars($quiz["title"]) ?></h3>
                    <span class="admin-assessment-course"><?= htmlspecialchars($quiz["course_title"]) ?></span>
                    <p><?= htmlspecialchars($quiz["description"]) ?></p>
                    <div class="admin-assessment-meta">
                        <span><?= (int)$quiz["question_count"] ?> questions</span>
                        <span><?= (int)$quiz["attempt_count"] ?> attempts</span>
                    </div>
                    <a href="../quiz.php?quiz_id=<?= (int)$quiz["id"] ?>" class="admin-edit-link">Preview quiz <span aria-hidden="true">-&gt;</span></a>
                </article>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="admin-assessment-section">
        <div class="admin-section-title">
            <div>
                <p class="eyebrow">PRACTICE</p>
                <h2><?= $exerciseResult->num_rows ?> exercises</h2>
            </div>
        </div>

        <div class="admin-exercise-list">
            <?php while ($exercise = $exerciseResult->fetch_assoc()): ?>
                <article class="admin-exercise-row">
                    <div>
                        <span class="admin-assessment-course"><?= htmlspecialchars($exercise["course_title"]) ?></span>
                        <h3><?= htmlspecialchars($exercise["title"]) ?></h3>
                    </div>
                    <p><?= htmlspecialchars($exercise["instructions"]) ?></p>
                </article>
            <?php endwhile; ?>
        </div>
    </section>
</main>

</body>
</html>
