<?php
// Temporary admin script to change user password
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';

$email = 'iraoui.e365@ucd.ac.ma';
$newPassword = 'elayachi123';

// Find user by email
$user = User::findByEmail($email);

if (!$user) {
    echo "Erreur: Utilisateur non trouvé avec l'email: " . htmlspecialchars($email) . "\n";
    exit(1);
}

// Update password
if (User::updatePassword($user['id'], $newPassword)) {
    echo "✓ Mot de passe changé avec succès pour " . htmlspecialchars($user['nom']) . " (" . htmlspecialchars($user['email']) . ")\n";
    echo "Nouveau mot de passe: " . htmlspecialchars($newPassword) . "\n";
} else {
    echo "✗ Erreur lors du changement de mot de passe\n";
    exit(1);
}
?>
