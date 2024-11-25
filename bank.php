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

// Je récupère le verbe HTTP de la requête : GET, PUT ou DELETE
$method = $_SERVER['REQUEST_METHOD'];
// Je récupère les données attachées à la requête
$data = json_decode(file_get_contents(filename: 'php://input'), true);

if ($method === 'GET') {
    // Fournir les informations d'une banque d'image
    $id = $_GET['id'];
    $database = db();
    $query = "SELECT bank.id AS bank_id, bank.name AS bank_name, bank.dir, bank.description, image.id AS image_id, image.name AS image_name
          FROM bank
          LEFT JOIN image ON bank.id = image.bankId
          WHERE bank.id = ?";
    $stmt = $database->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    // Récupérer le résultat
    $result = $stmt->get_result();
    // Parcourir les résultats
    $bank = null;
    $images = [];
    while ($row = $result->fetch_assoc()) {
        if (!$bank) {
            // Si la banque n'est pas encore définie, on la récupère
            $bank = [
                'id' => $row['bank_id'],
                'name' => $row['bank_name'],
                'description' => $row['description'],
                'dir' => $row['dir']
            ];
        }
        // Ajouter les images associées à la banque
        if ($row['image_id'] !== null) {
            $images[] = [
                'id' => $row['image_id'],
                'name' => $row['image_name'],
                'src' => $imageRootDir . $bank['dir'] . '/' . $row['image_name']
            ];
        }
    }
    if ($bank) {
        $bank['images'] = $images;
        echo json_encode(['success' =>true, 'bank' => $bank]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
    $database->close();
} elseif ($method === 'PUT') {
    // Créer une nouvelle banque d'images
    $name = $data['name'];
    $description = $data['description'];
    $id = $data['id'];

    $database = db();

    if (!empty($name) && !empty($description)) {
        $stmt = $database->prepare("UPDATE bank SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['sucess' => false]);
    }

    $stmt->close();
    $database->close();
} elseif ($method === 'POST') {
    // Créer une nouvelle banque d'images
    $name = $data['name'];
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
    $imageId = $data['imageId'];
    
    if($imageId) {
        // Je récupère les info de la banque d'images
        $query = "SELECT image.name as name, bank.dir as dir 
                  FROM image
                  LEFT JOIN bank
                  ON image.bankId = bank.id
                  WHERE image.id = ?";
        $stmt = $database->prepare($query);
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
        $status = false;
        $stmt->close();
        // Si la banque d'image existe alors
        if($image) {
            $imageDir = $image['dir'];
            $imageName = $image['name'];
            // On commence une transaction
            $database->begin_transaction();
            try {
                // Supprimer les images de Label
                $stmt = $database->prepare("DELETE FROM label WHERE imageId = ?");
                $stmt->bind_param("i", $imageId);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                $stmt->close();
                //Supprimer les images de CatalogImage
                $stmt = $database->prepare("DELETE FROM catalogImage WHERE imageId  = ?");
                $stmt->bind_param("i", $imageId);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                $stmt->close();
                // Supprimer les images de Image
                $stmt = $database->prepare("DELETE FROM image WHERE id = ?");
                $stmt->bind_param("i", $imageId);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                $stmt->close();
                // Si tout se passe bien, valider la transaction
                $database->commit();
                // et on supprime le fichier du répertoire
                unlink($imageRootDir . $imageDir . '/' . $imageName);
                $status = true;
            } catch (Exception $e) {
                // Si une erreur survient, annuler la transaction
                $database->rollback();
                $status = false;
            }
        }
    }

    $bankId = $data['bankId'];
    if ($bankId) {
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
            }
        }   
    }
    echo json_encode(['success' => $status]);
    $database->close();
}
