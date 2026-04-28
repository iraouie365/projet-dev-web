<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();
$message = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password_id'])) {
    $pwd_id = (int) $_POST['change_password_id'];
    $new_password = $_POST['new_password'] ?? '';
    
    if (strlen($new_password) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif (User::updatePassword($pwd_id, $new_password)) {
        $message = "Mot de passe changé avec succès.";
    } else {
        $message = "Erreur lors du changement de mot de passe.";
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = (int) $_POST['delete_id'];
    if ($del_id !== $user['id']) { // Prevent self-delete
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$del_id]);
            $message = "Utilisateur supprimé.";
        } catch (PDOException $e) {
            // Check only for ACTIVE records (not completed/rejected)
            $usageStmt = $pdo->prepare("
                SELECT
                    (SELECT COUNT(*) FROM demandes WHERE demandeur_id = ? AND statut NOT IN ('traitee', 'rejettee')) AS demandes_actives,
                    (SELECT COUNT(*) FROM demandes WHERE validateur_id = ? AND statut NOT IN ('traitee', 'rejettee')) AS demandes_validateur_actives,
                    (SELECT COUNT(*) FROM demandes WHERE admin_id = ? AND statut NOT IN ('traitee', 'rejettee')) AS demandes_admin_actives
            ");
            $usageStmt->execute([$del_id, $del_id, $del_id]);
            $usage = $usageStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $blockedReasons = [];
            if (!empty($usage['demandes_actives'])) {
                $blockedReasons[] = $usage['demandes_actives'] . ' demande(s) en cours';
            }
            if (!empty($usage['demandes_validateur_actives'])) {
                $blockedReasons[] = $usage['demandes_validateur_actives'] . ' demande(s) à valider';
            }
            if (!empty($usage['demandes_admin_actives'])) {
                $blockedReasons[] = $usage['demandes_admin_actives'] . ' demande(s) en traitement';
            }

            if ($blockedReasons) {
                $message = "Suppression impossible: cet utilisateur a encore " . implode(', ', $blockedReasons) . " en cours.";
            } else {
                // If no active records block deletion, try again
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$del_id]);
                    $message = "Utilisateur supprimé.";
                } catch (PDOException $e2) {
                    $message = "Suppression impossible: cet utilisateur est encore référencé par des données liées.";
                }
            }
        }
    } else {
        $message = "Erreur: Vous ne pouvez pas vous supprimer.";
    }
}

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
  $nom = trim($_POST['nom'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $role = $_POST['role'] ?? 'demandeur';
  $password = $_POST['password'] ?? '';
  $chef_id = !empty($_POST['chef_id']) ? (int) $_POST['chef_id'] : null;

  if ($nom === '' || $email === '') {
    $message = "Nom et email sont obligatoires.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "Email invalide.";
  }
    
  if ($message === '' && !empty($_POST['user_id'])) {
        // Update
        $user_id = (int) $_POST['user_id'];
    $existing = User::findByEmail($email);
    if ($existing && (int) $existing['id'] !== $user_id) {
      $message = "Cet email existe deja.";
    }

    if ($message !== '') {
      // Keep message set above.
    } else {
        $stmt = $pdo->prepare("
            UPDATE users SET nom=?, email=?, role=?, chef_id=? WHERE id=?
        ");
    if ($stmt->execute([$nom, $email, $role, $chef_id, $user_id])) {
            $message = "Utilisateur modifié.";
    } else {
      $message = "Erreur lors de la modification de l'utilisateur.";
    }
        }
  } elseif ($message === '') {
        // Create
    if (User::findByEmail($email)) {
      $message = "Cet email existe deja.";
    } elseif (strlen($password) < 6) {
            $message = "Le mot de passe doit contenir au moins 6 caractères.";
        } else {
      if (User::create($nom, $email, $password, $role, $chef_id)) {
        $message = "Utilisateur créé avec succès.";
      } else {
        $message = "Erreur lors de la création de l'utilisateur.";
      }
        }
    }
}

$users = User::all();
$editUser = null;
if (!empty($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    $editUser = User::findById($edit_id);
}
?>
<h1 class="mb-4">Gestion des utilisateurs</h1>

<?php if ($message): ?>
  <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-md-6">
    <h4><?php echo $editUser ? 'Modifier' : 'Ajouter'; ?> un utilisateur</h4>
    <form method="post" class="card p-3 mb-4">
      <?php if ($editUser): ?>
        <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
      <?php endif; ?>
      <div class="mb-2">
        <label class="form-label">Nom</label>
        <input type="text" name="nom" class="form-control" required value="<?php echo $editUser ? htmlspecialchars($editUser['nom']) : ''; ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?php echo $editUser ? htmlspecialchars($editUser['email']) : ''; ?>">
      </div>
      <?php if (!$editUser): ?>
      <div class="mb-2">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <?php endif; ?>
      <div class="mb-2">
        <label class="form-label">Rôle</label>
        <select name="role" class="form-select">
          <option value="demandeur" <?php echo ($editUser && $editUser['role'] === 'demandeur') ? 'selected' : ''; ?>>Demandeur</option>
          <option value="validateur" <?php echo ($editUser && $editUser['role'] === 'validateur') ? 'selected' : ''; ?>>Validateur</option>
          <option value="admin" <?php echo ($editUser && $editUser['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
      </div>
      <div class="mb-2">
        <label class="form-label">Chef (si demandeur)</label>
        <select name="chef_id" class="form-select">
          <option value="">-- Aucun --</option>
          <?php foreach ($users as $u): ?>
            <?php if ($u['role'] === 'validateur' && (!$editUser || $u['id'] !== $editUser['id'])): ?>
              <option value="<?php echo $u['id']; ?>" <?php echo ($editUser && $editUser['chef_id'] == $u['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($u['nom']); ?> (<?php echo $u['role']; ?>)
              </option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary" type="submit"><?php echo $editUser ? 'Modifier' : 'Créer'; ?></button>
      <?php if ($editUser): ?>
        <a href="admin_users.php" class="btn btn-secondary">Annuler</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="col-md-6">
    <h4>Liste des utilisateurs</h4>
    <table class="table table-sm table-users">
      <thead>
        <tr><th>#</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?php echo $u['id']; ?></td>
          <td><?php echo htmlspecialchars($u['nom']); ?></td>
          <td><?php echo htmlspecialchars($u['email']); ?></td>
          <td><?php echo htmlspecialchars($u['role']); ?></td>
          <td>
            <a href="?edit_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#passwordModal" data-user-id="<?php echo $u['id']; ?>" data-user-name="<?php echo htmlspecialchars($u['nom']); ?>">Mot de passe</button>
            <?php if ($u['id'] !== $user['id']): ?>
              <form method="post" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr?');">
                <input type="hidden" name="delete_id" value="<?php echo $u['id']; ?>">
                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal for changing password -->
<div class="modal fade" id="passwordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Changer le mot de passe</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <p class="mb-3">Utilisateur: <strong id="modalUserName"></strong></p>
          <div class="mb-3">
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" id="newPassword" name="new_password" class="form-control" required minlength="6" placeholder="Au moins 6 caractères">
          </div>
          <input type="hidden" id="changePasswordId" name="change_password_id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('passwordModal').addEventListener('show.bs.modal', function (e) {
  const button = e.relatedTarget;
  document.getElementById('changePasswordId').value = button.getAttribute('data-user-id');
  document.getElementById('modalUserName').textContent = button.getAttribute('data-user-name');
  document.getElementById('newPassword').value = '';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
