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

$userId = isset($_GET["id"])
    ? (int)$_GET["id"]
    : (int)($_POST["id"] ?? 0);

if ($userId <= 0) {
    header("Location: users.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $role = $_POST["role"] ?? "student";
    $password = $_POST["password"] ?? "";

    if ($fullName === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid name and email address.";
    } elseif (!in_array($role, ["student", "admin"], true)) {
        $error = "Invalid user role.";
    } elseif ($userId === (int)$_SESSION["user_id"] && $role !== "admin") {
        $error = "You cannot remove your own admin access.";
    } elseif ($password !== "" && strlen($password) < 6) {
        $error = "A new password must contain at least 6 characters.";
    } else {
        $duplicateStmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ? AND id <> ?"
        );
        $duplicateStmt->bind_param("si", $email, $userId);
        $duplicateStmt->execute();
        $duplicateExists = $duplicateStmt->get_result()->num_rows > 0;
        $duplicateStmt->close();

        if ($duplicateExists) {
            $error = "That email address is already in use.";
        } else {
            if ($password !== "") {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateSql = "
                    UPDATE users
                    SET full_name = ?, email = ?, role = ?, password = ?
                    WHERE id = ?
                ";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param(
                    "ssssi",
                    $fullName,
                    $email,
                    $role,
                    $hashedPassword,
                    $userId
                );
            } else {
                $updateSql = "
                    UPDATE users
                    SET full_name = ?, email = ?, role = ?
                    WHERE id = ?
                ";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param(
                    "sssi",
                    $fullName,
                    $email,
                    $role,
                    $userId
                );
            }

            if ($updateStmt->execute()) {
                if ($userId === (int)$_SESSION["user_id"]) {
                    $_SESSION["full_name"] = $fullName;
                    $_SESSION["email"] = $email;
                    $_SESSION["role"] = $role;
                }
                $updateStmt->close();
                header("Location: users.php");
                exit();
            }

            $error = "The user could not be updated.";
            $updateStmt->close();
        }
    }
}

$userStmt = $conn->prepare(
    "SELECT full_name, email, role FROM users WHERE id = ?"
);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user) {
    header("Location: users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || $error === "") {
    $fullName = $user["full_name"];
    $email = $user["email"];
    $role = $user["role"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
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

<main class="form-container admin-user-form">
    <p class="eyebrow">SYSTEM MANAGEMENT</p>
    <h1>Edit User</h1>

    <?php if ($error !== ""): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $userId ?>">

        <label for="full_name">Full Name</label>
        <input
            id="full_name"
            type="text"
            name="full_name"
            value="<?= htmlspecialchars($fullName) ?>"
            required
        >

        <label for="email">Email</label>
        <input
            id="email"
            type="email"
            name="email"
            value="<?= htmlspecialchars($email) ?>"
            required
        >

        <label for="role">Access Role</label>
        <select id="role" name="role" required>
            <option value="student" <?= $role === "student" ? "selected" : "" ?>>Student</option>
            <option value="admin" <?= $role === "admin" ? "selected" : "" ?>>Admin</option>
        </select>

        <label for="password">New Password <span class="admin-form-hint">(optional)</span></label>
        <input
            id="password"
            type="password"
            name="password"
            placeholder="Leave blank to keep current password"
        >

        <div class="admin-form-actions">
            <a href="users.php" class="admin-cancel-link">Cancel</a>
            <button type="submit">Save User</button>
        </div>
    </form>
</main>

</body>
</html>
