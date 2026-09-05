<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$fullName = $_SESSION["full_name"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>AI Assistant - ICT Study AI</title>

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

            <a href="chat.php" class="active" aria-current="page">
                AI Assistant
            </a>

            <a href="history.php">
                History
            </a>

            <a href="../auth/logout.php">
                Logout
            </a>

        </nav>

    </header>


    <main class="chat-page">

        <div class="chat-container">

            <div class="chat-header">

                <div class="chat-heading">
                    <div class="assistant-mark" aria-hidden="true">AI</div>
                    <div>
                        <p class="eyebrow">YOUR STUDY PARTNER</p>
                        <h1>Study Assistant</h1>
                    </div>
                </div>

                <span class="assistant-status">
                    <span aria-hidden="true"></span> Online and ready
                </span>

                <p class="chat-description">
                    Ask clear questions about ICT, programming, databases, networking, or cybersecurity.
                </p>

                <div class="chat-topic-cues" aria-label="Suggested topics">
                    <span>Explain a concept</span>
                    <span>Review my code</span>
                    <span>Give me an exercise</span>
                </div>

            </div>


            <div
                class="chat-messages"
                id="chatMessages"
            >

                <div class="message ai-message">

                    <p>
                        Hello <?php echo htmlspecialchars($fullName); ?>!
                        How can I help you learn ICT today?
                    </p>

                </div>

            </div>


            <form
                class="chat-form"
                id="chatForm"
            >

                <label class="chat-input-label" for="userMessage">
                    Ask your next question
                </label>

                <input
                    type="text"
                    id="userMessage"
                    placeholder="Ask your ICT question..."
                    autocomplete="off"
                    required
                >

                <button type="submit" aria-label="Send question">
                    <span>Send</span>
                    <span aria-hidden="true">-&gt;</span>
                </button>

            </form>

        </div>

    </main>


    <script src="../js/script.js"></script>

</body>

</html>