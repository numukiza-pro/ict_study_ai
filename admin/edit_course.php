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

$courseId = isset($_GET["id"])
    ? (int)$_GET["id"]
    : (int)($_POST["id"] ?? 0);

if ($courseId <= 0) {
    header("Location: courses.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($title === "") {
        $error = "Course title is required.";
    } else {
        $updateSql = "
            UPDATE courses
            SET title = ?, description = ?
            WHERE id = ?
        ";

        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param(
            "ssi",
            $title,
            $description,
            $courseId
        );

        if ($updateStmt->execute()) {
            $updateStmt->close();
            header("Location: courses.php");
            exit();
        }

        $error = "The course could not be updated.";
        $updateStmt->close();
    }
}

$courseStmt = $conn->prepare(
    "SELECT title, description FROM courses WHERE id = ?"
);
$courseStmt->bind_param("i", $courseId);
$courseStmt->execute();
$course = $courseStmt->get_result()->fetch_assoc();
$courseStmt->close();

if (!$course) {
    header("Location: courses.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || $error !== "") {
    $title = $course["title"];
    $description = $course["description"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar course-navbar">
    <div class="logo">ICT Study AI Admin</div>

    <div class="nav-links">
        <a href="index.php">Admin Dashboard</a>
        <a href="courses.php" class="active" aria-current="page">Courses</a>
        <a href="lessons.php">Lessons</a>
        <a href="../auth/logout.php">Logout</a>
    </div>
</nav>

<main class="form-container admin-course-form">
    <p class="eyebrow">COURSE MANAGEMENT</p>
    <h1>Edit Course</h1>

    <?php if ($error !== ""): ?>
        <p class="error-message">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $courseId ?>">

        <label for="title">Course Title</label>
        <input
            id="title"
            type="text"
            name="title"
            value="<?= htmlspecialchars($title) ?>"
            required
        >

        <label for="description">Description</label>
        <textarea
            id="description"
            name="description"
            rows="7"
        ><?= htmlspecialchars($description) ?></textarea>

        <div class="admin-form-actions">
            <a href="courses.php" class="admin-cancel-link">Cancel</a>
            <button type="submit">Save Course</button>
        </div>
    </form>
</main>

</body>
</html>
