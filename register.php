<?php
require "db/database.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$login = $data['login'];
$email = $data['email'];
$firstName = $data['firstName'];
$lastName = $data['lastName'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$userRoleId = $data['userRoleId'];

$database = db();
$stmt = $database->prepare("INSERT INTO useraccount (login, email, firstName, lastName, password, userRoleId) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sssssi', $login, $email, $firstName, $lastName, $password, $userRoleId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'login' => $login]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription.']);
}

$stmt->close();
$database->close();
