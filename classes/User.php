<?php
require_once __DIR__ . '/Database.php';

class User {
    public static function findByEmail($email) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function all() {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($nom,$email,$password,$role,$chef_id=null) {
        $pdo = Database::getInstance();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO users (nom,email,password,role,chef_id)
            VALUES (?,?,?,?,?)
        ");
        return $stmt->execute([$nom,$email,$hash,$role,$chef_id]);
    }
}
