<?php
require "db/database.php";

session_start();

header('Content-Type: application/json');

if(!isset($_SESSION['user']) && $_SESSION['user']['userRole'] != 'editor') {
    http_response_code(401);
}

if (isset($_GET)) {
    $database = db();
    $result = $database->query("SELECT * FROM bank ORDER BY id DESC;");
    $banks = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['sucess' => true, 'banks' => $banks]);
}

