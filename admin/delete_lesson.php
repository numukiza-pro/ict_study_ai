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


$sql = "
    DELETE FROM lessons
    WHERE id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$stmt->close();


header("Location: lessons.php");

exit();

?>