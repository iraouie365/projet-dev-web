<?php
class Database {
    private static $instance = null;
    public $pdo;

    private function __construct() {
        require __DIR__ . '/../config/db.php';
        $this->pdo = $pdo;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}