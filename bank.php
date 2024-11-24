<?php
require "db/database.php";

session_start();

header('Content-Type: application/json');

$imageRootDir = 'images/'; 

if(!isset($_SESSION['user']) && $_SESSION['user']['userRole'] != 'editor') {
    http_response_code(401);
}

// Générer une chaîne de caractère aléatoire
function generateRandomString($length = 30) {
    // Calculer la longueur réelle en octets (chaque octet est converti en 2 caractères hexadécimaux)
    $bytes = ceil($length / 2);
    return substr(bin2hex(random_bytes($bytes)), 0, $length);
}

// Suppression d'un répertoire en récursif (par défaut php ne peux supprimer qu'un répertoire vide)
function delTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

// Je récupère le verbe HTTP de la requête
$method = $_SERVER['REQUEST_METHOD'];
// Je récupère les données attachées à la requête
$data = json_decode(file_get_contents(filename: 'php://input'), true);

if ($method === 'POST') {
    // Créer une nouvelle banque d'images
    $name =$data['name'];
    $description = $data['description'];
    $dir = generateRandomString(30);

    $database = db();

    if (!empty($name) && !empty($description)) {
        $stmt = $database->prepare("INSERT INTO bank (name, dir, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $dir, $description);
        $stmt->execute();
        echo json_encode(['success' => true]);
        // On crée le répertoire pour stocker les images sur le serveur
        mkdir($imageRootDir . $dir, 0777, true);
    } else {
        http_response_code(400);
        echo json_encode(['sucess' => false]);
    }

    $stmt->close();
    $database->close();
} elseif ($method === 'DELETE') {
    // Supprimer une banque d'images
    $database = db();

    $bankId = $data['bankId'];
    // Je récupère les info de la banque d'images
    $stmt = $database->prepare("SELECT * FROM BANK WHERE id = ?");
    $stmt->bind_param("i", $bankId);
    $stmt->execute();
    $result = $stmt->get_result();
    $bank = $result->fetch_assoc();
    $status = false;
    $stmt->close();
    // Si la banque d'image existe alors
    if($bank) {
        // 
        // Démarrer la transaction
        $database->begin_transaction();
        try {
            // Supprimer les images de Label
            $stmt = $database->prepare("DELETE FROM label WHERE imageId IN (SELECT id FROM image WHERE bankId = ?)");
            $stmt->bind_param("i", $bankId);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            //Supprimer les images de CatalogImage
            $stmt = $database->prepare("DELETE FROM catalogImage WHERE imageId IN (SELECT id FROM image WHERE bankId = ?)");
            $stmt->bind_param("i", $bankId);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            // Supprimer les images de Image
            $stmt = $database->prepare("DELETE FROM image WHERE bankId = ?");
            $stmt->bind_param("i", $bankId);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            // Supprimer la banque d'images
            $stmt = $database->prepare("DELETE FROM bank WHERE id = ?");
            $stmt->bind_param("i", $bankId);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            // Si tout se passe bien, valider la transaction
            $database->commit();
            // et on supprime le répertoire de la banque d'image du serveur
            delTree($imageRootDir . $bank['dir']);
            $status = true;
        } catch (Exception $e) {
            // Si une erreur survient, annuler la transaction
            $database->rollback();
            $status = false;
        } finally {
            $database->close();
        }
    }   
    echo json_encode(['success' => $status]);
}
