<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

require_once "config/database.php";

$sql = "
    SELECT
        c.id,
        c.title,
        c.description,
        COALESCE(MAX(qa.percentage), 0) AS best_score
    FROM courses c
    LEFT JOIN quizzes q
        ON q.course_id = c.id
    LEFT JOIN quiz_attempts qa
        ON qa.quiz_id = q.id
        AND qa.user_id = ?
    GROUP BY c.id, c.title, c.description
    ORDER BY c.title ASC
";

$stmt = $conn->prepare($sql);
$userId = (int)$_SESSION["user_id"];
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>ICT Courses</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<nav class="navbar course-navbar">

    <div class="logo">
        ICT Study AI
    </div>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="courses.php" class="active" aria-current="page">
            Courses
        </a>

        <a href="progress.php">
            My Progress
        </a>

        <a href="ai/chat.php">
            AI Assistant
        </a>

        <a href="ai/history.php">
            History
        </a>

        <a href="auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<main class="courses-page">

    <div class="courses-header">

        <div>
            <p class="eyebrow">LEARNING LIBRARY</p>
            <h1>Build skills that move with you.</h1>

            <p class="courses-intro">
                Choose a focused path, work through the lessons, and test what you know with practical quizzes.
            </p>
        </div>

        <div class="courses-header-mark" aria-hidden="true">
            <span>ICT</span>
        </div>

    </div>

    <div class="course-library-meta">
        <span><?= $result->num_rows ?> learning paths</span>
        <span>Lessons + practice + quizzes</span>
    </div>


    <div class="courses-grid">

        <?php while ($course = $result->fetch_assoc()): ?>

            <article class="course-card">

                <div class="course-card-topline">
                    <span class="course-number">
                        <?= str_pad((string)$course["id"], 2, "0", STR_PAD_LEFT) ?>
                    </span>
                    <span class="course-label">COURSE PATH</span>
                </div>

                <h2>
                    <?= htmlspecialchars($course["title"]) ?>
                </h2>

                <p>
                    <?= htmlspecialchars($course["description"]) ?>
                </p>

                <div class="course-card-footer">
                    <div class="course-actions">
                        <span>Learn at your pace</span>
                        <?php if ((float)$course["best_score"] >= 70): ?>
                            <a
                                href="certificate.php?course_id=<?= $course["id"] ?>"
                                class="certificate-button"
                            >
                                Get certificate
                            </a>
                        <?php else: ?>
                            <span class="certificate-locked">
                                Certificate locked
                            </span>
                        <?php endif; ?>
                    </div>
                    <a
                    href="lessons.php?course_id=<?= $course["id"] ?>"
                    class="course-button"
                    >
                        Explore course <span aria-hidden="true">-&gt;</span>
                    </a>
                </div>

            </article>

        <?php endwhile; ?>

    </div>

</main>

</body>

</html>