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

$message = "";


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
| Add lesson
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $courseId = intval($_POST["course_id"]);
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);


    if ($courseId <= 0 || $title === "" || $content === "") {

        $message = "All fields are required.";

    } else {

        $sql = "
            INSERT INTO lessons
            (course_id, title, content)
            VALUES (?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "iss",
            $courseId,
            $title,
            $content
        );

        $stmt->execute();

        $stmt->close();


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

    <title>Add Lesson</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<div class="form-container">

    <h1>Add New Lesson</h1>


    <?php if ($message !== ""): ?>

        <p class="error-message">
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <label>
            Course
        </label>

        <select name="course_id" required>

            <option value="">
                Select Course
            </option>

            <?php while ($course = $coursesResult->fetch_assoc()): ?>

                <option value="<?= $course["id"] ?>">

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
            placeholder="Enter lesson title"
            required
        >


        <label>
            Lesson Content
        </label>

        <textarea
            name="content"
            rows="12"
            placeholder="Write lesson content here..."
            required
        ></textarea>


        <button type="submit">
            Add Lesson
        </button>

    </form>


    <br>

    <a href="lessons.php">
        Back to Lessons
    </a>

</div>

</body>

</html>