<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

require_once "config/database.php";


if (!isset($_GET["course_id"])) {

    header("Location: courses.php");

    exit();
}


$courseId = intval($_GET["course_id"]);


/*
|--------------------------------------------------------------------------
| Get course
|--------------------------------------------------------------------------
*/

$courseSql = "
    SELECT id, title, description
    FROM courses
    WHERE id = ?
";

$courseStmt = $conn->prepare($courseSql);

$courseStmt->bind_param(
    "i",
    $courseId
);

$courseStmt->execute();

$courseResult = $courseStmt->get_result();

$course = $courseResult->fetch_assoc();

$courseStmt->close();


if (!$course) {

    header("Location: courses.php");

    exit();
}


/*
|--------------------------------------------------------------------------
| Get lessons
|--------------------------------------------------------------------------
*/

$lessonSql = "
    SELECT id, title, content
    FROM lessons
    WHERE course_id = ?
    ORDER BY id ASC
";

$lessonStmt = $conn->prepare($lessonSql);

$lessonStmt->bind_param(
    "i",
    $courseId
);

$lessonStmt->execute();

$lessonResult = $lessonStmt->get_result();

$exerciseSql = "
    SELECT title, instructions
    FROM course_exercises
    WHERE course_id = ?
    ORDER BY id ASC
";

$exerciseStmt = $conn->prepare($exerciseSql);

$exerciseStmt->bind_param(
    "i",
    $courseId
);

$exerciseStmt->execute();

$exerciseResult = $exerciseStmt->get_result();

$quizSql = "
    SELECT id, title, description
    FROM quizzes
    WHERE course_id = ?
    ORDER BY id ASC
";

$quizStmt = $conn->prepare($quizSql);

$quizStmt->bind_param(
    "i",
    $courseId
);

$quizStmt->execute();

$quizResult = $quizStmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($course["title"]) ?>
        Lessons
    </title>

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


<main class="lessons-page">

    <div class="lessons-header">

        <div>
            <a class="back-link" href="courses.php">&lt;- Back to courses</a>
            <p class="eyebrow">COURSE PATH</p>
            <h1><?= htmlspecialchars($course["title"]) ?></h1>

            <p><?= htmlspecialchars($course["description"]) ?></p>
        </div>

        <div class="course-summary" aria-label="Course contents">
            <strong>Learn</strong>
            <span>Lessons, practice, then quiz</span>
        </div>

    </div>

    <section class="course-content-section">
        <div class="section-heading">
            <p class="eyebrow">01 / CORE CONTENT</p>
            <h2>Lessons for this path</h2>
        </div>

        <div class="lessons-list">

            <?php while ($lesson = $lessonResult->fetch_assoc()): ?>

                <article class="lesson-card">
                    <span class="lesson-index">Lesson</span>
                    <h2><?= htmlspecialchars($lesson["title"]) ?></h2>
                    <p><?= nl2br(htmlspecialchars($lesson["content"])) ?></p>
                </article>

            <?php endwhile; ?>

        </div>
    </section>

    <section class="course-content-section practice-band">

        <div class="section-heading">
            <p class="eyebrow">02 / HANDS-ON PRACTICE</p>
            <h2>Try it before you test it.</h2>
        </div>

    <div class="exercise-section">

        <?php while ($exercise = $exerciseResult->fetch_assoc()): ?>

            <div class="exercise-card">

                <h3>
                    <?= htmlspecialchars($exercise["title"]) ?>
                </h3>

                <p>
                    <?= nl2br(
                        htmlspecialchars($exercise["instructions"])
                    ) ?>
                </p>

            </div>

        <?php endwhile; ?>

    </div>

    </section>

    <section class="course-content-section quiz-section">

        <div class="section-heading">
            <p class="eyebrow">03 / CHECKPOINTS</p>
            <h2>Ready to check your progress?</h2>
        </div>

    <?php while ($quiz = $quizResult->fetch_assoc()): ?>

        <article class="quiz-card">

            <h3>
                <?= htmlspecialchars($quiz["title"]) ?>
            </h3>

            <p>
                <?= htmlspecialchars($quiz["description"]) ?>
            </p>

            <a
                href="quiz.php?quiz_id=<?= $quiz["id"] ?>"
                class="course-button"
            >
                Start Quiz
            </a>

        </article>

    <?php endwhile; ?>

</section>

</main>

</body>

</html>

<?php
$lessonStmt->close();
$exerciseStmt->close();
$quizStmt->close();

?>