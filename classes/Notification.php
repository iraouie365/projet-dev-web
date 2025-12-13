<?php
require_once __DIR__ . '/Database.php';

class Notification {
    public static function create($user_id, $message) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id,message) VALUES (?,?)");
        return $stmt->execute([$user_id, $message]);
    }

    public static function findByUser($user_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countUnread($user_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int) $stmt->fetchColumn();
    }

    public static function markAllRead($user_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    }
}
