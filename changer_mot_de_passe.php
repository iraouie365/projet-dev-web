<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/User.php';

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $freshUser = User::findById($user['id']);

    if (!$freshUser || !password_verify($currentPassword, $freshUser['password'])) {
        $message = 'Le mot de passe actuel est incorrect.';
        $messageType = 'danger';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        $messageType = 'danger';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'La confirmation du mot de passe ne correspond pas.';
        $messageType = 'danger';
    } elseif (User::updatePassword($user['id'], $newPassword)) {
        $_SESSION['user'] = User::findById($user['id']);
        $user = $_SESSION['user'];
        $message = 'Mot de passe modifié avec succès.';
        $messageType = 'success';
    } else {
        $message = 'Erreur lors de la modification du mot de passe.';
        $messageType = 'danger';
    }
}
?>
<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-shield-lock me-2"></i>Changer le mot de passe
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">Votre mot de passe doit être confirmé avec le mot de passe actuel avant d'être modifié.</p>

        <?php if ($message): ?>
          <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Mot de passe actuel</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" name="new_password" class="form-control" minlength="6" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmer le nouveau mot de passe</label>
            <input type="password" name="confirm_password" class="form-control" minlength="6" required>
          </div>
          <button type="submit" class="btn btn-primary">Mettre à jour</button>
          <a href="<?php echo ($user['role'] === 'demandeur' ? 'dashboard_demandeur.php' : ($user['role'] === 'validateur' ? 'dashboard_validateur.php' : 'dashboard_admin.php')); ?>" class="btn btn-secondary ms-2">Retour</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>