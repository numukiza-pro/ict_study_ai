<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {

    header("Location: auth/login.php");
    exit();
}

$userId = (int)$_SESSION["user_id"];

if (isset($_SESSION["full_name"]) && $_SESSION["full_name"] !== "") {
    $fullName = $_SESSION["full_name"];
} else {
    $userStmt = $conn->prepare(
        "SELECT full_name FROM users WHERE id = ?"
    );

    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    if (!$user) {
        session_unset();
        session_destroy();
        header("Location: auth/login.php");
        exit();
    }

    $fullName = $user["full_name"];
    $_SESSION["full_name"] = $fullName;
}

$quizResult = $conn->query(
    "SELECT id FROM quizzes ORDER BY id ASC LIMIT 1"
);

$starterQuiz = $quizResult ? $quizResult->fetch_assoc() : null;

$courseCountResult = $conn->query(
    "SELECT COUNT(*) AS total FROM courses"
);
$courseCount = $courseCountResult
    ? (int)$courseCountResult->fetch_assoc()["total"]
    : 0;

$attemptStmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM quiz_attempts WHERE user_id = ?"
);
$attemptStmt->bind_param("i", $userId);
$attemptStmt->execute();
$attemptCount = (int)$attemptStmt->get_result()->fetch_assoc()["total"];
$attemptStmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - ICT Study AI</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

    <header class="navbar dashboard-navbar">

        <div class="logo">
            ICT Study AI
        </div>

        <nav class="dashboard-nav-links">

            <a href="dashboard.php" class="active" aria-current="page">
                Dashboard
            </a>

            <a href="courses.php">
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

            <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                <a href="admin/index.php">
                    Admin Panel
                </a>
            <?php endif; ?>

            <a href="auth/logout.php">
                Logout
            </a>

        </nav>

    </header>

    <main class="dashboard">

        <section class="dashboard-hero">
            <div>
                <p class="eyebrow">YOUR ICT LEARNING SPACE</p>
                <h1>
                    Welcome back, <?= htmlspecialchars($fullName) ?>.
                </h1>

                <p>
                    Build practical skills, ask better questions, and keep moving through your learning path.
                </p>

                <div class="dashboard-hero-actions">
                    <a href="courses.php" class="dashboard-primary-button">
                        Browse courses <span aria-hidden="true">-&gt;</span>
                    </a>
                    <a href="ai/chat.php" class="dashboard-secondary-button">
                        Ask the AI assistant
                    </a>
                </div>
            </div>

            <div class="dashboard-hero-badge" aria-hidden="true">
                <span>LEARN</span>
                <strong>01</strong>
            </div>
        </section>

        <section class="dashboard-stats" aria-label="Learning overview">
            <div>
                <strong><?= $courseCount ?></strong>
                <span>Learning paths</span>
            </div>
            <div>
                <strong><?= $attemptCount ?></strong>
                <span>Quiz attempts</span>
            </div>
            <div>
                <strong>24/7</strong>
                <span>AI support</span>
            </div>
        </section>

        <div class="dashboard-section-heading">
            <div>
                <p class="eyebrow">CHOOSE YOUR NEXT MOVE</p>
                <h2>Learn in the way that suits you.</h2>
            </div>
        </div>

        <div class="dashboard-grid">

            <article class="dashboard-card dashboard-card-ai">

                <h2>AI Assistant</h2>

                <p>
                    Ask questions about ICT and programming.
                </p>

                <a href="ai/chat.php">
                    Start Learning
                </a>

            </article>

            <article class="dashboard-card dashboard-card-course">

                <h2>ICT Courses</h2>

                <p>
                    Learn programming, databases,
                    networking and cybersecurity.
                </p>

                <a href="courses.php">
                    View Courses
                </a>

            </article>

            <article class="dashboard-card dashboard-card-quiz">

                <h2>Practice Quiz</h2>

                <p>
                    Test your ICT knowledge with quizzes.
                </p>

                <a href="<?= $starterQuiz ? "quiz.php?quiz_id=" . (int)$starterQuiz["id"] : "courses.php" ?>">
                    Start Quiz
                </a>

            </article>

            <article class="dashboard-card dashboard-card-progress">

                <h2>My Progress</h2>

                <p>
                    Track your learning progress.
                </p>

                <a href="progress.php">
                    View Progress
                </a>

                </article>

        </div>

    </main>

</body>

</html>