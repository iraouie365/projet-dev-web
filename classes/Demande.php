<?php
require_once __DIR__ . '/Database.php';

class Demande {
    public static function create($demandeur_id,$type_id,$description,$urgence,$validateur_id=null) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO demandes (demandeur_id,type_id,description,urgence,validateur_id)
            VALUES (?,?,?,?,?)
        ");
        $stmt->execute([$demandeur_id,$type_id,$description,$urgence,$validateur_id]);
        return $pdo->lastInsertId();
    }

    public static function update($id,$type_id,$description,$urgence) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            UPDATE demandes
            SET type_id=?, description=?, urgence=?, updated_at=NOW()
            WHERE id=? AND statut IN ('en_attente','en_cours_validation')
        ");
        return $stmt->execute([$type_id,$description,$urgence,$id]);
    }

    public static function findByDemandeur($demandeur_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT d.*, t.libelle as type_libelle
            FROM demandes d
            JOIN types_besoins t ON d.type_id = t.id
            WHERE demandeur_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$demandeur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findForValidateur($validateur_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT d.*, u.nom as demandeur_nom, t.libelle as type_libelle
            FROM demandes d
            JOIN users u ON d.demandeur_id = u.id
            JOIN types_besoins t ON d.type_id = t.id
            WHERE u.chef_id = ?
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$validateur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function all() {
        $pdo = Database::getInstance();
        $sql = "
          SELECT d.*, u.nom AS demandeur_nom, t.libelle AS type_libelle
          FROM demandes d
          JOIN users u ON d.demandeur_id = u.id
          JOIN types_besoins t ON d.type_id = t.id
          ORDER BY d.created_at DESC
        ";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function changerStatut($id,$statut,$admin_id=null) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            UPDATE demandes
            SET statut=?, admin_id=?, updated_at=NOW()
            WHERE id=?
        ");
        return $stmt->execute([$statut,$admin_id,$id]);
    }

    public static function delete($id) {
        $pdo = Database::getInstance();
        try {
            // Delete related validation records first
            $stmt = $pdo->prepare("DELETE FROM validation WHERE demande_id = ?");
            $stmt->execute([$id]);
            
            // Delete related attached files
            $stmt = $pdo->prepare("DELETE FROM pieces_jointes WHERE demande_id = ?");
            $stmt->execute([$id]);
            
            // Delete the demande itself
            $stmt = $pdo->prepare("DELETE FROM demandes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
