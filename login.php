<?php
require "db/database.php";

session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$login = $data['login'];
$password = $data['password'];

$database = db();

$stmt = $database->prepare("SELECT login, email, firstName, lastName, password, userRoleId, userrole.name FROM useraccount JOIN userrole ON useraccount.userRoleId=userrole.id WHERE login = ?");
$stmt->bind_param('s', $login);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($db_login, $db_email, $db_firstName, $db_lastName, $db_password, $userRoleId, $userRole);
$stmt->fetch();

if ($stmt->num_rows > 0 && password_verify($password, $db_password)) {
    $_SESSION['user'] = [
        'login' => $db_login,
        'email' => $db_email,
        'firstName' => $db_firstName,
        'lastName' => $db_lastName,
        'userRoleId' => $userRoleId,
        'userRole' => $userRole
    ];
    $user = $_SESSION['user'];
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Login ou mot de passe incorrect.']);
}

$stmt->close();
$database->close();
