<?php
class Database {
    private static $instance = null;
    public $pdo;

    private function __construct() {
        require __DIR__ . '/../config/db.php';
        $this->pdo = (isset($pdo) && $pdo instanceof PDO) ? $pdo : $this->createPdoFromConfig();
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

    private function createPdoFromConfig() {
        require __DIR__ . '/../config/config.php';
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}