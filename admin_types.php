<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Database.php';

// Only admins can access this page
if ($user['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$pdo = Database::getInstance();
$message = '';

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['type_id'])) {
    $libelle = trim($_POST['libelle'] ?? '');
    if ($libelle) {
        $stmt = $pdo->prepare("INSERT INTO types_besoins (libelle) VALUES (?)");
        if ($stmt->execute([$libelle])) {
            $message = "Type ajouté avec succès.";
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM types_besoins WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $message = "Type supprimé.";
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_id'])) {
    $type_id = (int) $_POST['type_id'];
    $libelle = trim($_POST['libelle'] ?? '');
    if ($libelle) {
        $stmt = $pdo->prepare("UPDATE types_besoins SET libelle = ? WHERE id = ?");
        if ($stmt->execute([$libelle, $type_id])) {
            $message = "Type modifié avec succès.";
        }
    }
}

$types = $pdo->query("SELECT * FROM types_besoins ORDER BY libelle")->fetchAll(PDO::FETCH_ASSOC);
$editType = null;
if (!empty($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM types_besoins WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editType = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<h1 class="mb-4">Gestion des types de besoins</h1>

<?php if ($message): ?>
  <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-md-6">
    <h4><?php echo $editType ? 'Modifier' : 'Ajouter'; ?> un type</h4>
    <form method="post" class="card p-3 mb-4">
      <?php if ($editType): ?>
        <input type="hidden" name="type_id" value="<?php echo $editType['id']; ?>">
      <?php endif; ?>
      <div class="mb-3">
        <label class="form-label">Libellé du type</label>
        <input type="text" name="libelle" class="form-control" required 
               value="<?php echo $editType ? htmlspecialchars($editType['libelle']) : ''; ?>" 
               placeholder="ex: Matériel, Logiciel, Service, Formation...">
      </div>
      <button class="btn btn-primary" type="submit"><?php echo $editType ? 'Modifier' : 'Ajouter'; ?></button>
      <?php if ($editType): ?>
        <a href="admin_types.php" class="btn btn-secondary">Annuler</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="col-md-6">
    <h4>Liste des types (<?php echo count($types); ?>)</h4>
    <table class="table table-sm">
      <thead>
        <tr><th>Libellé</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($types as $t): ?>
        <tr>
          <td><?php echo htmlspecialchars($t['libelle']); ?></td>
          <td>
            <a href="?edit_id=<?php echo $t['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
            <form method="post" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr?');">
              <input type="hidden" name="delete_id" value="<?php echo $t['id']; ?>">
              <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
