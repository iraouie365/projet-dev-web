<?php
class Database {
    private static $instance = null;
    public $pdo;

    private function __construct() {
        require __DIR__ . '/../config/db.php';
        $this->pdo = (isset($pdo) && $pdo instanceof PDO) ? $pdo : $this->createPdoFromConfig();
        $this->ensureServicesTable();
        $this->ensureDemandesServiceColumn();
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

    private function ensureDemandesServiceColumn() {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM demandes LIKE 'service'");
        $columnExists = $stmt && $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$columnExists) {
            $this->pdo->exec("ALTER TABLE demandes ADD COLUMN service VARCHAR(100) NULL AFTER admin_id");
        }
    }

    private function createPdoFromConfig() {
        $host = getenv('DB_HOST') ?: 'mysql-eliraoui.alwaysdata.net';
        $dbname = getenv('DB_NAME') ?: 'eliraoui_gdeb';
        $user = getenv('DB_USER') ?: 'eliraoui';
        $pass = getenv('DB_PASS') ?: 'iraoui0101';
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}