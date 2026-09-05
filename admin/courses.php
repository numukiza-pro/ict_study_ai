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
        courses.id,
        courses.title,
        courses.description,
        COUNT(DISTINCT lessons.id) AS lesson_count,
        COUNT(DISTINCT quizzes.id) AS quiz_count
    FROM courses
    LEFT JOIN lessons
        ON lessons.course_id = courses.id
    LEFT JOIN quizzes
        ON quizzes.course_id = courses.id
    GROUP BY courses.id, courses.title, courses.description
    ORDER BY courses.id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Courses</title>

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

        <a href="courses.php" class="active" aria-current="page">
            Courses
        </a>

        <a href="lessons.php">
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


<main class="admin-courses-page">

    <div class="admin-courses-header">

        <div>
            <p class="eyebrow">CONTENT WORKSPACE</p>
            <h1>Shape every course.</h1>
            <p>Organize learning paths and keep the student experience focused.</p>
        </div>

        <a href="add_course.php" class="admin-primary-button">
            Add course <span aria-hidden="true">+</span>
        </a>

    </div>

    <div class="admin-courses-meta">
        <span><?= $result->num_rows ?> courses in your library</span>
        <span>Lessons and quizzes at a glance</span>
    </div>

    <div class="admin-courses-grid">

        <?php if ($result->num_rows > 0): ?>

        <?php while ($course = $result->fetch_assoc()): ?>

            <article class="admin-course-card">

                <div class="admin-course-topline">
                    <span class="admin-course-number">
                        <?= str_pad((string)$course["id"], 2, "0", STR_PAD_LEFT) ?>
                    </span>
                    <span>COURSE PATH</span>
                </div>

                <h2>
                    <?= htmlspecialchars($course["title"]) ?>
                </h2>

                <p>
                    <?= htmlspecialchars($course["description"]) ?>
                </p>

                <div class="admin-course-meta">
                    <span><?= (int)$course["lesson_count"] ?> lessons</span>
                    <span><?= (int)$course["quiz_count"] ?> quizzes</span>
                </div>

                <div class="admin-course-actions">

                    <a
                        href="edit_course.php?id=<?= $course["id"] ?>"
                        class="admin-edit-link"
                    >
                        Edit course <span aria-hidden="true">-&gt;</span>
                    </a>


                    <a
                        href="delete_course.php?id=<?= $course["id"] ?>"
                        class="admin-delete-link"
                        onclick="return confirm('Are you sure you want to delete this course?');"
                    >
                        Delete
                    </a>

                </div>

            </article>

        <?php endwhile; ?>

        <?php else: ?>
            <div class="admin-empty-state">
                <p class="eyebrow">NO COURSES YET</p>
                <h2>Start your learning library.</h2>
                <p>Create the first course for your students.</p>
                <a href="add_course.php" class="admin-primary-button">Add first course</a>
            </div>
        <?php endif; ?>

    </div>

</main>

</body>

</html>