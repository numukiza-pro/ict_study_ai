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


if (!isset($_GET["id"])) {

    header("Location: lessons.php");

    exit();
}


$id = intval($_GET["id"]);


/*
|--------------------------------------------------------------------------
| Get lesson
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id, course_id, title, content
    FROM lessons
    WHERE id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();

$lesson = $result->fetch_assoc();

$stmt->close();


if (!$lesson) {

    header("Location: lessons.php");

    exit();
}


/*
|--------------------------------------------------------------------------
| Get courses
|--------------------------------------------------------------------------
*/

$coursesSql = "
    SELECT id, title
    FROM courses
    ORDER BY title ASC
";

$coursesResult = $conn->query($coursesSql);


/*
|--------------------------------------------------------------------------
| Update lesson
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $courseId = intval($_POST["course_id"]);
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);


    if ($courseId <= 0 || $title === "" || $content === "") {

        $error = "All fields are required.";

    } else {

        $updateSql = "
            UPDATE lessons
            SET course_id = ?,
                title = ?,
                content = ?
            WHERE id = ?
        ";

        $updateStmt = $conn->prepare($updateSql);

        $updateStmt->bind_param(
            "issi",
            $courseId,
            $title,
            $content,
            $id
        );

        $updateStmt->execute();

        $updateStmt->close();


        header("Location: lessons.php");

        exit();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Lesson</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<div class="form-container">

    <h1>Edit Lesson</h1>


    <?php if (isset($error)): ?>

        <p class="error-message">
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <label>
            Course
        </label>

        <select name="course_id" required>

            <?php while ($course = $coursesResult->fetch_assoc()): ?>

                <option
                    value="<?= $course["id"] ?>"
                    <?= $course["id"] == $lesson["course_id"]
                        ? "selected"
                        : "" ?>
                >

                    <?= htmlspecialchars($course["title"]) ?>

                </option>

            <?php endwhile; ?>

        </select>


        <label>
            Lesson Title
        </label>

        <input
            type="text"
            name="title"
            value="<?= htmlspecialchars($lesson["title"]) ?>"
            required
        >


        <label>
            Lesson Content
        </label>

        <textarea
            name="content"
            rows="12"
            required
        ><?= htmlspecialchars($lesson["content"]) ?></textarea>


        <button type="submit">
            Update Lesson
        </button>

    </form>


    <br>

    <a href="lessons.php">
        Back to Lessons
    </a>

</div>

</body>

</html>