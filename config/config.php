<?php
// Fichier : config/config.php
// NE PAS ENVOYER SUR GITHUB

$host = 'host';
$dbname = 'eliraoui_gdeb';
$user = 'eliraoui';
$pass = 'iraoui0101';


<?php
// Fichier : config/db.php

// On inclut les variables secrètes
require_once __DIR__ . '/config.php';      

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion BD : " . $e->getMessage());
}
