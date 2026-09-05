<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

$userId = $_SESSION["user_id"];

$tableCheck = $conn->query("SHOW TABLES LIKE 'chat_history'");

if ($tableCheck->num_rows === 0) {
    $result = false;
} else {

    $sql = "
    SELECT
        user_message,
        ai_response,
        created_at
    FROM chat_history
    WHERE user_id = ?
    ORDER BY created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $userId);

    $stmt->execute();

    $result = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Chat History - ICT Study AI</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<header class="navbar assistant-navbar">

    <div class="logo">
        ICT Study AI
    </div>

    <nav class="assistant-nav-links">

        <a href="../dashboard.php">
            Dashboard
        </a>

        <a href="../courses.php">
            Courses
        </a>

        <a href="../progress.php">
            My Progress
        </a>

        <a href="chat.php">
            AI Assistant
        </a>

        <a href="history.php" class="active" aria-current="page">
            History
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </nav>

</header>


<main class="history-page assistant-history-page">

    <section class="history-header">
        <div>
            <p class="eyebrow">YOUR STUDY ARCHIVE</p>
            <h1>Questions worth returning to.</h1>
            <p>
                Revisit your conversations and keep useful explanations close while you learn.
            </p>
        </div>

        <a href="chat.php" class="history-primary-button">
            Start a new chat <span aria-hidden="true">-&gt;</span>
        </a>
    </section>

    <?php if ($result && $result->num_rows > 0): ?>

        <?php while ($chat = $result->fetch_assoc()): ?>

            <article class="history-card">

                <div class="history-card-topline">
                    <span>Study conversation</span>
                    <time datetime="<?= htmlspecialchars($chat["created_at"]) ?>">
                        <?= htmlspecialchars(date("M j, Y · g:i A", strtotime($chat["created_at"]))) ?>
                    </time>
                </div>

                <div class="history-question">

                    <span class="history-label">Question</span>

                    <p>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $chat["user_message"]
                            )
                        );
                        ?>
                    </p>

                </div>


                <div class="history-answer">

                    <span class="history-label">Response</span>

                    <p>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $chat["ai_response"]
                            )
                        );
                        ?>
                    </p>

                </div>


            </article>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="history-empty-state">
            <p class="eyebrow">YOUR ARCHIVE IS CLEAR</p>
            <h2>No conversations yet.</h2>
            <p>Ask the assistant about a concept, your code, or a new practice exercise.</p>
            <a href="chat.php" class="history-primary-button">Ask your first question</a>
        </div>

    <?php endif; ?>

</main>

</body>

</html>