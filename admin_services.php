<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Database.php';

if ($user['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$pdo = Database::getInstance();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['service_id'])) {
    $nom = trim($_POST['nom'] ?? '');
    if ($nom) {
        $stmt = $pdo->prepare("INSERT INTO services (nom) VALUES (?)");
        if ($stmt->execute([$nom])) {
            $message = "Service ajouté avec succès.";
        } else {
            $message = "Erreur: Ce service existe peut-être déjà.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $message = "Service supprimé.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
    $service_id = (int) $_POST['service_id'];
    $nom = trim($_POST['nom'] ?? '');
    if ($nom) {
        $stmt = $pdo->prepare("UPDATE services SET nom = ? WHERE id = ?");
        if ($stmt->execute([$nom, $service_id])) {
            $message = "Service modifié avec succès.";
        } else {
            $message = "Erreur: Ce service existe peut-être déjà.";
        }
    }
}

$services = $pdo->query("SELECT * FROM services ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$editService = null;
if (!empty($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editService = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<h1 class="mb-4">Gestion des services</h1>

<?php if ($message): ?>
  <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-md-6">
    <h4><?php echo $editService ? 'Modifier' : 'Ajouter'; ?> un service</h4>
    <form method="post" class="card p-3 mb-4">
      <?php if ($editService): ?>
        <input type="hidden" name="service_id" value="<?php echo $editService['id']; ?>">
      <?php endif; ?>
      <div class="mb-3">
        <label class="form-label">Nom du service</label>
        <input type="text" name="nom" class="form-control" required 
               value="<?php echo $editService ? htmlspecialchars($editService['nom']) : ''; ?>" 
               placeholder="ex: Achat, DSI, RH, Logistique, Finances, Direction...">
      </div>
      <button class="btn btn-primary" type="submit"><?php echo $editService ? 'Modifier' : 'Ajouter'; ?></button>
      <?php if ($editService): ?>
        <a href="admin_services.php" class="btn btn-secondary">Annuler</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="col-md-6">
    <h4>Services disponibles (<?php echo count($services); ?>)</h4>
    <div class="list-group">
      <?php foreach ($services as $s): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <strong><?php echo htmlspecialchars($s['nom']); ?></strong>
          </div>
          <div>
            <a href="?edit_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
            <form method="post" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr? Les demandes assignées à ce service conserveront cette valeur.');">
              <input type="hidden" name="delete_id" value="<?php echo $s['id']; ?>">
              <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
