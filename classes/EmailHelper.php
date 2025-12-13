<?php
require_once __DIR__ . '/../config/email.php';

class EmailHelper {
    
    public static function send($recipientEmail, $subject, $message) {
        if (!defined(constant_name: 'MAIL_ENABLED') || !MAIL_ENABLED) {
            return true; 
        }

        $headers = "From: " . (defined(constant_name: 'MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'System') . " <" . (defined('MAIL_FROM') ? MAIL_FROM : 'noreply@localhost') . ">\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail($recipientEmail, $subject, $message, $headers);
    }

    
    public static function notifyValidation($demandeurEmail, $demandeurNom, $demandeId, $action, $commentaire = '') {
        $actionFr = ($action === 'valide') ? 'validée' : 'rejetée';
        $subject = "Demande #{$demandeId} {$actionFr}";
        $message = "Bonjour {$demandeurNom},\n\n";
        $message .= "Votre demande #{$demandeId} a été {$actionFr}.\n";
        if ($commentaire) {
            $message .= "Commentaire: " . $commentaire . "\n";
        }
        $message .= "\nCordialement,\nSystème de Gestion des Besoins";

        return self::send($demandeurEmail, $subject, nl2br($message));
    }

    public static function notifyStatusChange($demandeurEmail, $demandeurNom, $demandeId, $newStatus) {
        $statusFr = self::translateStatus($newStatus);
        $subject = "Demande #{$demandeId} - Statut changé: {$statusFr}";
        $message = "Bonjour {$demandeurNom},\n\n";
        $message .= "Le statut de votre demande #{$demandeId} a été changé en: {$statusFr}.\n";
        $message .= "\nCordialement,\nSystème de Gestion des Besoins";

        return self::send($demandeurEmail, $subject, nl2br($message));
    }

    private static function translateStatus($status) {
        $translations = [
            'en_attente' => 'En attente',
            'en_cours_validation' => 'En cours de validation',
            'validee' => 'Validée',
            'rejettee' => 'Rejetée',
            'traitee' => 'Traitée'
        ];
        return $translations[$status] ?? $status;
    }
}
?>
