<?php
session_start();

// Détruire la session côté serveur
session_destroy();

// Réponse JSON pour confirmer la déconnexion
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit;
?>
