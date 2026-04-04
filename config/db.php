<?php
// Fichier : config/db.php

// Priorite aux variables d'environnement; fallback vers les valeurs de prod.
$host = getenv('DB_HOST') ?: 'mysql-eliraoui.alwaysdata.net';
$dbname = getenv('DB_NAME') ?: 'eliraoui_gdeb';
$user = getenv('DB_USER') ?: 'eliraoui';
$pass = getenv('DB_PASS') ?: 'iraoui0101';

try {
	$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Erreur connexion BD : " . $e->getMessage());
}


