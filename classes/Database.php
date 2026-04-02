<?php
class Database {
    private static $instance = null;
    public $pdo;

    private function __construct() {
        require __DIR__ . '/../config/db.php';
        $this->pdo = $pdo;
        $this->ensureServicesTable();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }

    private function ensureServicesTable() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL UNIQUE
        )");
    }
}