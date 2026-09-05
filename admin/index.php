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

$courseCount = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM courses"
)->fetch_assoc()["total"];

$lessonCount = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM lessons"
)->fetch_assoc()["total"];

$quizCount = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM quizzes"
)->fetch_assoc()["total"];

$userCount = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM users"
)->fetch_assoc()["total"];

$exerciseCount = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM course_exercises"
)->fetch_assoc()["total"];

$questionCount = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM questions"
)->fetch_assoc()["total"];

$attemptCount = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM quiz_attempts"
)->fetch_assoc()["total"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<nav class="navbar admin-navbar">

    <div class="logo">
        ICT Study AI Admin
    </div>

    <div class="nav-links">

        <a href="../dashboard.php">
            Student Dashboard
        </a>

        <a href="courses.php">
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


<main class="admin-home">

    <section class="admin-home-hero">
        <div>
            <p class="eyebrow">ICT STUDY AI / ADMIN</p>
            <h1>Build a better learning library.</h1>
            <p>
                Keep courses, lessons, and assessments clear, current, and ready for students.
            </p>
        </div>

        <div class="admin-home-mark" aria-hidden="true">
            <span>CMS</span>
        </div>
    </section>

    <section class="admin-stats" aria-label="System overview">
        <div>
            <strong><?= $courseCount ?></strong>
            <span>Courses</span>
        </div>
        <div>
            <strong><?= $lessonCount ?></strong>
            <span>Lessons</span>
        </div>
        <div>
            <strong><?= $quizCount ?></strong>
            <span>Quizzes</span>
        </div>
        <div>
            <strong><?= $userCount ?></strong>
            <span>Users</span>
        </div>
        <div>
            <strong><?= $exerciseCount ?></strong>
            <span>Exercises</span>
        </div>
        <div>
            <strong><?= $questionCount ?></strong>
            <span>Questions</span>
        </div>
        <div>
            <strong><?= $attemptCount ?></strong>
            <span>Attempts</span>
        </div>
    </section>

    <div class="admin-home-heading">
        <p class="eyebrow">CONTENT CONTROL</p>
        <h2>Choose a workspace.</h2>
    </div>

    <section class="admin-home-grid">

        <article class="admin-home-card admin-home-course-card">
            <span class="admin-card-index">01 / COURSES</span>

            <h2>Courses</h2>

            <p>
                Manage ICT courses.
            </p>

            <a href="courses.php" class="admin-primary-button">
                Open course manager <span aria-hidden="true">-&gt;</span>
            </a>

        </article>


        <article class="admin-home-card admin-home-lesson-card">
            <span class="admin-card-index">02 / LESSONS</span>

            <h2>Lessons</h2>

            <p>
                Create and manage course lessons.
            </p>

            <a href="lessons.php" class="admin-primary-button">
                Open lesson manager <span aria-hidden="true">-&gt;</span>
            </a>

        </article>

        <article class="admin-home-card admin-home-user-card">
            <span class="admin-card-index">03 / USERS</span>

            <h2>Users</h2>

            <p>
                View accounts, manage roles, and review learning activity.
            </p>

            <a href="users.php" class="admin-primary-button">
                Open user manager <span aria-hidden="true">-&gt;</span>
            </a>

        </article>

        <article class="admin-home-card admin-home-assessment-card">
            <span class="admin-card-index">04 / ASSESSMENTS</span>

            <h2>Assessments</h2>

            <p>
                Review quizzes, questions, attempts, and practice exercises.
            </p>

            <a href="assessments.php" class="admin-primary-button">
                Open assessment manager <span aria-hidden="true">-&gt;</span>
            </a>

        </article>

    </section>

</main>

</body>

</html>