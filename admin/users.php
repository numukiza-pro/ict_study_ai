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

$chatTable = $conn->query("SHOW TABLES LIKE 'chat_history'");
$chatHistoryAvailable = $chatTable->num_rows > 0;

$chatSelect = $chatHistoryAvailable
    ? "COUNT(DISTINCT ch.id) AS chat_messages"
    : "0 AS chat_messages";

$chatJoin = $chatHistoryAvailable
    ? "LEFT JOIN chat_history ch ON ch.user_id = u.id"
    : "";

$sql = "
    SELECT
        u.id,
        u.full_name,
        u.email,
        u.role,
        u.created_at,
        COUNT(DISTINCT qa.id) AS quiz_attempts,
        $chatSelect
    FROM users u
    LEFT JOIN quiz_attempts qa
        ON qa.user_id = u.id
    $chatJoin
    GROUP BY u.id, u.full_name, u.email, u.role, u.created_at
    ORDER BY u.created_at DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar admin-navbar">
    <div class="logo">ICT Study AI Admin</div>

    <div class="nav-links">
        <a href="index.php">Admin Dashboard</a>
        <a href="courses.php">Courses</a>
        <a href="lessons.php">Lessons</a>
        <a href="assessments.php">Assessments</a>
        <a href="users.php" class="active" aria-current="page">Users</a>
        <a href="../auth/logout.php">Logout</a>
    </div>
</nav>

<main class="admin-users-page">
    <section class="admin-users-header">
        <div>
            <p class="eyebrow">SYSTEM MANAGEMENT</p>
            <h1>Know who is learning.</h1>
            <p>View accounts, monitor activity, and keep access details up to date.</p>
        </div>
    </section>

    <div class="admin-users-meta">
        <span><?= $result->num_rows ?> registered users</span>
        <span>Manage access responsibly</span>
    </div>

    <section class="admin-users-table-wrap">
        <?php if ($result->num_rows > 0): ?>
            <div class="admin-users-table-scroll">
                <table class="admin-users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Access</th>
                            <th>Activity</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="admin-user-identity">
                                        <span class="admin-user-avatar">
                                            <?= strtoupper(substr($user["full_name"], 0, 1)) ?>
                                        </span>
                                        <div>
                                            <strong><?= htmlspecialchars($user["full_name"]) ?></strong>
                                            <span><?= htmlspecialchars($user["email"]) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-role-badge <?= $user["role"] === "admin" ? "admin-role" : "student-role" ?>">
                                        <?= htmlspecialchars(ucfirst($user["role"])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-activity-count">
                                        <?= (int)$user["quiz_attempts"] ?> quizzes
                                    </span>
                                    <span class="admin-activity-count">
                                        <?= (int)$user["chat_messages"] ?> chats
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars(date("M j, Y", strtotime($user["created_at"]))) ?>
                                </td>
                                <td>
                                    <div class="admin-user-actions">
                                        <a href="edit_user.php?id=<?= (int)$user["id"] ?>" class="admin-edit-link">
                                            Edit
                                        </a>
                                        <?php if ((int)$user["id"] !== (int)$_SESSION["user_id"]): ?>
                                            <a
                                                href="delete_user.php?id=<?= (int)$user["id"] ?>"
                                                class="admin-delete-link"
                                                onclick="return confirm('Delete this user and their activity?');"
                                            >
                                                Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="admin-current-user">You</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty-state">
                <p class="eyebrow">NO USERS YET</p>
                <h2>No accounts have been registered.</h2>
                <p>New student accounts will appear here.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
