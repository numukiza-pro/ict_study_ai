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


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);


    if ($title === "") {

        $message = "Course title is required.";

    } else {

        $sql = "
            INSERT INTO courses
            (title, description)
            VALUES (?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ss",
            $title,
            $description
        );

        $stmt->execute();

        $stmt->close();


        header("Location: courses.php");

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

    <title>Add Course</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<div class="form-container">

    <h1>Add New Course</h1>


    <?php if ($message !== ""): ?>

        <p class="error-message">
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <label>
            Course Title
        </label>

        <input
            type="text"
            name="title"
            placeholder="Enter course title"
            required
        >


        <label>
            Description
        </label>

        <textarea
            name="description"
            placeholder="Enter course description"
            rows="6"
        ></textarea>


        <button type="submit">
            Add Course
        </button>

    </form>


    <br>

    <a href="courses.php">
        Back to Courses
    </a>

</div>

</body>

</html>