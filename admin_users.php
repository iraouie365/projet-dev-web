<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();
$message = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = (int) $_POST['delete_id'];
    if ($del_id !== $user['id']) { // Prevent self-delete
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$del_id]);
            $message = "Utilisateur supprimé.";
        } catch (PDOException $e) {
            $usageStmt = $pdo->prepare("
                SELECT
                    (SELECT COUNT(*) FROM demandes WHERE demandeur_id = ?) AS demandes_demandeur,
                    (SELECT COUNT(*) FROM demandes WHERE validateur_id = ?) AS demandes_validateur,
                    (SELECT COUNT(*) FROM demandes WHERE admin_id = ?) AS demandes_admin,
                    (SELECT COUNT(*) FROM validation WHERE validateur_id = ?) AS validations
            ");
            $usageStmt->execute([$del_id, $del_id, $del_id, $del_id]);
            $usage = $usageStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $blockedReasons = [];
            if (!empty($usage['demandes_demandeur'])) {
                $blockedReasons[] = $usage['demandes_demandeur'] . ' demande(s)';
            }
            if (!empty($usage['demandes_validateur'])) {
                $blockedReasons[] = $usage['demandes_validateur'] . ' demande(s) en tant que validateur';
            }
            if (!empty($usage['demandes_admin'])) {
                $blockedReasons[] = $usage['demandes_admin'] . ' demande(s) traitée(s) en tant qu\'admin';
            }
            if (!empty($usage['validations'])) {
                $blockedReasons[] = $usage['validations'] . ' validation(s)';
            }

            if ($blockedReasons) {
                $message = "Suppression impossible: cet utilisateur est encore lié à " . implode(', ', $blockedReasons) . ".";
            } else {
                $message = "Suppression impossible: cet utilisateur est encore référencé par des données liées.";
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
