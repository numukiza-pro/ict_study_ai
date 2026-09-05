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

$userId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$currentUserId = (int)$_SESSION["user_id"];

if ($userId > 0 && $userId !== $currentUserId) {
    $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->bind_param("i", $userId);
    $deleteStmt->execute();
    $deleteStmt->close();
}

header("Location: users.php");
exit();
