<?php

$localConfig = __DIR__ . "/database.local.php";

if (is_file($localConfig)) {
    require $localConfig;
}

$host = $host ?? getenv("DB_HOST") ?: "localhost";
$username = $username ?? getenv("DB_USER") ?: "root";
$password = $password ?? getenv("DB_PASSWORD") ?: "";
$database = $database ?? getenv("DB_NAME") ?: "ict_study_ai";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>