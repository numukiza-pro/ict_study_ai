<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

require_once "config/database.php";

$courseId = isset($_GET["course_id"])
    ? (int)$_GET["course_id"]
    : 0;
$userId = (int)$_SESSION["user_id"];

$sql = "
    SELECT
        c.title,
        qa.percentage,
        qa.created_at
    FROM courses c
    INNER JOIN quizzes q
        ON q.course_id = c.id
    INNER JOIN quiz_attempts qa
        ON qa.quiz_id = q.id
    WHERE c.id = ?
        AND qa.user_id = ?
        AND qa.percentage >= 70
    ORDER BY qa.percentage DESC, qa.created_at DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $courseId, $userId);
$stmt->execute();
$certificate = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$certificate) {
    header("Location: courses.php");
    exit();
}

$studentName = $_SESSION["full_name"];
$certificateNumber = "ICT-" . str_pad((string)$courseId, 2, "0", STR_PAD_LEFT)
    . "-" . str_pad((string)$userId, 4, "0", STR_PAD_LEFT);
$completedDate = date("F j, Y", strtotime($certificate["created_at"]));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?= htmlspecialchars($certificate["title"]) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="certificate-body">

<nav class="navbar course-navbar certificate-nav">
    <div class="logo">ICT Study AI</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="courses.php" class="active" aria-current="page">Courses</a>
        <a href="progress.php">My Progress</a>
        <a href="ai/chat.php">AI Assistant</a>
        <a href="ai/history.php">History</a>
        <a href="auth/logout.php">Logout</a>
    </div>
</nav>

<main class="certificate-page">
    <div class="certificate-actions">
        <a class="back-link" href="courses.php">&lt;- Back to courses</a>
        <button class="course-button" type="button" onclick="window.print()">
            Print certificate
        </button>
    </div>

    <section class="certificate-card">
        <div class="certificate-edge"></div>
        <p class="eyebrow">ICT STUDY AI / CERTIFICATE OF COMPLETION</p>
        <h1>Certificate of<br>Achievement</h1>
        <p class="certificate-presented">This certificate is proudly presented to</p>
        <p class="certificate-name"><?= htmlspecialchars($studentName) ?></p>
        <p class="certificate-presented">for successfully completing the learning path</p>
        <h2><?= htmlspecialchars($certificate["title"]) ?></h2>
        <p class="certificate-score">
            Best quiz score: <strong><?= round((float)$certificate["percentage"]) ?>%</strong>
        </p>
        <div class="certificate-footer">
            <span>Completed <?= htmlspecialchars($completedDate) ?></span>
            <span><?= htmlspecialchars($certificateNumber) ?></span>
        </div>
    </section>
</main>

</body>
</html>
