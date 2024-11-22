<?php
    // Activation du rapport d'erreur (conseillé dans le doc officielle)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    /* // Récupératoin des paramètres de connexion
    $json = file_get_contents('./db/database_info.json');
    $db_infos=json_decode($json, true);
    // Connexion à la base
    $db = new mysqli(
        $db_infos['host'],
        $db_infos['username'],
        $db_infos['password'],
        $db_infos['database'],
        $db_infos['port']
    );
    // Vérification des éventuelles erreurs de connexion
    if ($db->connect_errno) {
        throw new RuntimeException('db connection error: ' . $db->connect_error);
    } */
    
    function db() {
        $json = file_get_contents('./db/database_info.json');
        $db_infos=json_decode($json, true);
        // Connexion à la base
        $db = new mysqli(
            $db_infos['host'],
            $db_infos['username'],
            $db_infos['password'],
            $db_infos['database'],
            $db_infos['port']
        );
        // Vérification des éventuelles erreurs de connexion
        if ($db->connect_errno) {
            throw new RuntimeException('db connection error: ' . $db->connect_error);
        }
        return $db;
    }