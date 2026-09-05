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

$sql = "
    SELECT
        lessons.id,
        lessons.title,
        lessons.content,
        courses.title AS course_title
    FROM lessons
    INNER JOIN courses
        ON lessons.course_id = courses.id
    ORDER BY lessons.id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Lessons</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<nav class="navbar admin-navbar">

    <div class="logo">
        ICT Study AI Admin
    </div>

    <div class="nav-links">

        <a href="index.php">
            Admin Dashboard
        </a>

        <a href="courses.php">
            Courses
        </a>

        <a href="lessons.php" class="active" aria-current="page">
            Lessons
        </a>

        <a href="assessments.php">
            Assessments
        </a>

        <a href="users.php">
            Users
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<main class="admin-lessons-page">

    <div class="admin-lessons-header">

        <div>
            <p class="eyebrow">CONTENT WORKSPACE</p>
            <h1>Shape every lesson.</h1>

            <p>
                Create, refine, and organize the learning content behind each ICT course.
            </p>

        </div>

        <a
            href="add_lesson.php"
            class="admin-primary-button"
        >
            Add lesson <span aria-hidden="true">+</span>
        </a>

    </div>


    <div class="admin-lessons-meta">
        <span><?= $result->num_rows ?> lessons in your library</span>
        <span>Keep content clear and practical</span>
    </div>

    <div class="admin-lessons-list">

        <?php if ($result->num_rows > 0): ?>

        <?php while ($lesson = $result->fetch_assoc()): ?>

            <article class="admin-lesson-card">

                <div class="admin-lesson-index">
                    <?= str_pad((string)$lesson["id"], 2, "0", STR_PAD_LEFT) ?>
                </div>

                <div class="admin-lesson-content">

                <h2>
                    <?= htmlspecialchars($lesson["title"]) ?>
                </h2>

                <span class="admin-lesson-course">
                    <?= htmlspecialchars($lesson["course_title"]) ?>
                </span>

                <p class="admin-lesson-preview">
                    <?= htmlspecialchars($lesson["content"]) ?>
                </p>

                </div>

                <div class="admin-lesson-actions">

                <a
                    href="edit_lesson.php?id=<?= $lesson["id"] ?>"
                    class="admin-edit-link"
                >
                    Edit lesson <span aria-hidden="true">-&gt;</span>
                </a>


                <a
                    href="delete_lesson.php?id=<?= $lesson["id"] ?>"
                    class="admin-delete-link"
                    onclick="return confirm('Are you sure you want to delete this lesson?');"
                >
                    Delete
                </a>

                </div>

            </article>

        <?php endwhile; ?>

        <?php else: ?>

            <div class="admin-empty-state">
                <p class="eyebrow">NO LESSONS YET</p>
                <h2>Start building your course library.</h2>
                <p>Add the first lesson and give students a clear place to begin.</p>
                <a href="add_lesson.php" class="admin-primary-button">Add first lesson</a>
            </div>

        <?php endif; ?>

    </div>

</main>

</body>

</html>