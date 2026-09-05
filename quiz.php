<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

require_once "config/database.php";

if (!isset($_GET["quiz_id"])) {
    header("Location: dashboard.php");
    exit();
}

$quizId = intval($_GET["quiz_id"]);


/*
|--------------------------------------------------------------------------
| Get quiz
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        quizzes.id,
        quizzes.title,
        quizzes.description,
        courses.title AS course_title
    FROM quizzes
    INNER JOIN courses
        ON quizzes.course_id = courses.id
    WHERE quizzes.id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $quizId
);

$stmt->execute();

$result = $stmt->get_result();

$quiz = $result->fetch_assoc();

$stmt->close();


if (!$quiz) {
    header("Location: dashboard.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get questions
|--------------------------------------------------------------------------
*/

$questionSql = "
    SELECT
        id,
        question,
        option_a,
        option_b,
        option_c,
        option_d
    FROM questions
    WHERE quiz_id = ?
    ORDER BY id ASC
    LIMIT 25
";

$questionStmt = $conn->prepare($questionSql);

$questionStmt->bind_param(
    "i",
    $quizId
);

$questionStmt->execute();

$questions = $questionStmt
    ->get_result()
    ->fetch_all(MYSQLI_ASSOC);

$questionStmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($quiz["title"]) ?>
    </title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<nav class="navbar course-navbar">

    <div class="logo">
        ICT Study AI
    </div>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="courses.php" class="active" aria-current="page">
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

        <a href="auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<main class="quiz-page">

    <div class="quiz-header">

    <a class="back-link" href="courses.php">&lt;- Back to courses</a>
    <p class="eyebrow">QUIZ CHECKPOINT</p>

        <h1>
            <?= htmlspecialchars($quiz["title"]) ?>
        </h1>

        <p>
            <?= htmlspecialchars($quiz["description"]) ?>
        </p>

        <div class="quiz-context">
            <span>Course</span>
            <strong><?= htmlspecialchars($quiz["course_title"]) ?></strong>
            <span class="quiz-count"><?= count($questions) ?> questions</span>
        </div>

    </div>


    <form
        action="submit_quiz.php"
        method="POST"
        class="quiz-form"
    >

        <input
            type="hidden"
            name="quiz_id"
            value="<?= $quizId ?>"
        >


        <?php foreach ($questions as $index => $question): ?>

            <fieldset class="question-card" data-question-index="<?= $index ?>">

                <legend>

                    <?= $index + 1 ?>.

                    <?= htmlspecialchars(
                        $question["question"]
                    ) ?>

                </legend>


                <label>

                    <input
                        type="radio"
                        name="answers[<?= $question["id"] ?>]"
                        value="A"
                        required
                    >

                    <?= htmlspecialchars(
                        $question["option_a"]
                    ) ?>

                </label>


                <label>

                    <input
                        type="radio"
                        name="answers[<?= $question["id"] ?>]"
                        value="B"
                    >

                    <?= htmlspecialchars(
                        $question["option_b"]
                    ) ?>

                </label>


                <label>

                    <input
                        type="radio"
                        name="answers[<?= $question["id"] ?>]"
                        value="C"
                    >

                    <?= htmlspecialchars(
                        $question["option_c"]
                    ) ?>

                </label>


                <label>

                    <input
                        type="radio"
                        name="answers[<?= $question["id"] ?>]"
                        value="D"
                    >

                    <?= htmlspecialchars(
                        $question["option_d"]
                    ) ?>

                </label>

            </fieldset>

        <?php endforeach; ?>


        <div class="quiz-navigation">
            <button
                type="button"
                class="quiz-nav-button quiz-previous"
            >
                Previous
            </button>

            <span class="quiz-step" aria-live="polite"></span>

            <button
                type="button"
                class="course-button quiz-next"
            >
                Next
            </button>

            <button
                type="submit"
                class="course-button quiz-submit"
            >
                Submit Quiz
            </button>
        </div>

    </form>

</main>

<script>
    const quizForm = document.querySelector(".quiz-form");
    const questionCards = Array.from(
        document.querySelectorAll(".question-card")
    );
    const previousButton = document.querySelector(".quiz-previous");
    const nextButton = document.querySelector(".quiz-next");
    const submitButton = document.querySelector(".quiz-submit");
    const stepLabel = document.querySelector(".quiz-step");
    const questionsPerPage = 2;
    const pageCount = Math.ceil(
        questionCards.length / questionsPerPage
    );
    let currentPage = 0;

    function showPage(page) {
        currentPage = page;

        questionCards.forEach((card, index) => {
            const visible = Math.floor(index / questionsPerPage) === currentPage;
            card.hidden = !visible;

            card.querySelectorAll("input").forEach((input) => {
                input.disabled = !visible;
            });
        });

        previousButton.disabled = currentPage === 0;
        nextButton.hidden = currentPage === pageCount - 1;
        submitButton.hidden = currentPage !== pageCount - 1;
        stepLabel.textContent = `Step ${currentPage + 1} of ${pageCount}`;
    }

    function currentPageComplete() {
        const visibleCards = questionCards.filter(
            (card) => !card.hidden
        );

        return visibleCards.every((card) => {
            return card.querySelector("input[type=radio]:checked");
        });
    }

    function allQuestionsComplete() {
        return questionCards.every((card) => {
            return card.querySelector("input[type=radio]:checked");
        });
    }

    nextButton.addEventListener("click", () => {
        if (!currentPageComplete()) {
            alert("Please answer both questions before continuing.");
            return;
        }

        showPage(currentPage + 1);
    });

    previousButton.addEventListener("click", () => {
        showPage(currentPage - 1);
    });

    quizForm.addEventListener("submit", (event) => {
        if (!currentPageComplete()) {
            event.preventDefault();
            alert("Please answer the questions on this step before submitting.");
            return;
        }

        if (!allQuestionsComplete()) {
            event.preventDefault();
            alert("Please use Previous to answer every question.");
            return;
        }

        questionCards.forEach((card) => {
            card.querySelectorAll("input").forEach((input) => {
                input.disabled = false;
            });
        });
    });

    showPage(0);
</script>

</body>

</html>