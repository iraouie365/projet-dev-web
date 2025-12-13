<?php
$host = 'localhost';
$dbname = 'gestion_besoins';
$user = 'root';    
$pass = '';         

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion BD : " . $e->getMessage());
}
