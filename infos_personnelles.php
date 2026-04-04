<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/User.php';

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));

    if ($nom === '' || $email === '') {
        $message = 'Le nom et l\'email sont obligatoires.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email invalide.';
        $messageType = 'danger';
    } else {
        $existing = User::findByEmail($email);
        if ($existing && (int) $existing['id'] !== (int) $user['id']) {
            $message = 'Cet email est deja utilise par un autre utilisateur.';
            $messageType = 'danger';
        } elseif (User::updateProfile($user['id'], $nom, $email)) {
            $_SESSION['user'] = User::findById($user['id']);
            $user = $_SESSION['user'];
            $message = 'Vos informations personnelles ont ete mises a jour.';
            $messageType = 'success';
        } else {
            $message = 'Erreur lors de la mise a jour du profil.';
            $messageType = 'danger';
        }
    }
}
?>

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-person-vcard me-2"></i>Informations personnelles
      </div>
      <div class="card-body">
        <?php if ($message): ?>
          <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" required value="<?php echo htmlspecialchars($user['nom']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
          </div>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>