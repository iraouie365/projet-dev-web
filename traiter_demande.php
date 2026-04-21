<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Demande.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Notification.php';
require_once __DIR__ . '/classes/EmailHelper.php';

$pdo = Database::getInstance();
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    header('Location: dashboard_admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newStatut = $_POST['statut'];
  $service = !empty($_POST['service']) ? $_POST['service'] : null;
  
  // Update both status and service
  $stmt = $pdo->prepare("UPDATE demandes SET statut=?, service=?, admin_id=?, updated_at=NOW() WHERE id=?");
  $stmt->execute([$newStatut, $service, $user['id'], $id]);
  
  // Notify the demandeur
  $q = $pdo->prepare("SELECT d.demandeur_id, u.email, u.nom FROM demandes d JOIN users u ON d.demandeur_id = u.id WHERE d.id = ?");
  $q->execute([$id]);
  $row = $q->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $demandeur_id = $row['demandeur_id'];
    $msg = "Le statut de votre demande #{$id} a été changé en: {$newStatut}.";
    if ($service) $msg .= " Service assigné: {$service}.";
    Notification::create($demandeur_id, $msg);
    // Try to send email (will silently skip if not configured)
    EmailHelper::notifyStatusChange($row['email'], $row['nom'], $id, $newStatut);
  }
  header('Location: dashboard_admin.php');
  exit;
}

// Get demande details
$stmt = $pdo->prepare("
  SELECT d.*, u.nom AS demandeur_nom, t.libelle AS type_libelle
  FROM demandes d
  JOIN users u ON d.demandeur_id = u.id
  JOIN types_besoins t ON d.type_id = t.id
  WHERE d.id = ?
");
$stmt->execute([$id]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

// Get attached files
$files = [];
$stmt = $pdo->prepare("SELECT * FROM pieces_jointes WHERE demande_id = ?");
$stmt->execute([$id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available services
$services = $pdo->query("SELECT * FROM services ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="mb-4">Traitement de la demande #<?php echo $demande['id']; ?></h1>

<div class="card mb-3">
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <p><strong>Demandeur :</strong> <?php echo htmlspecialchars($demande['demandeur_nom']); ?></p>
        <p><strong>Type :</strong> <?php echo htmlspecialchars($demande['type_libelle']); ?></p>
        <p><strong>Urgence :</strong> 
          <span class="badge 
            <?php 
              if ($demande['urgence'] === 'urgente') echo 'bg-danger';
              elseif ($demande['urgence'] === 'moyenne') echo 'bg-warning';
              else echo 'bg-info';
            ?>">
            <?php echo htmlspecialchars($demande['urgence']); ?>
          </span>
        </p>
      </div>
      <div class="col-md-6">
        <p><strong>Statut actuel :</strong> 
          <span class="badge 
            <?php 
              if ($demande['statut'] === 'traitee') echo 'bg-success';
              elseif ($demande['statut'] === 'rejettee') echo 'bg-danger';
              elseif ($demande['statut'] === 'validee') echo 'bg-info';
              else echo 'bg-warning';
            ?>">
            <?php echo htmlspecialchars($demande['statut']); ?>
          </span>
        </p>
        <p><strong>Date de création :</strong> <?php echo date('d/m/Y H:i', strtotime($demande['created_at'])); ?></p>
        <?php $serviceAssigne = $demande['service'] ?? null; ?>
        <p><strong>Service assigné :</strong> <?php echo $serviceAssigne ? htmlspecialchars($serviceAssigne) : '<em class="text-muted">Non assigné</em>'; ?></p>
      </div>
    </div>

    <hr>
    <p><strong>Description :</strong></p>
    <p><?php echo nl2br(htmlspecialchars($demande['description'])); ?></p>

    <?php if (!empty($files)): ?>
      <hr>
      <p><strong>Pièce(s) jointe(s) :</strong></p>
      <ul class="mb-0">
        <?php foreach ($files as $f): ?>
          <?php
            $size = '';
            $path = __DIR__ . '/' . $f['chemin'];
            if (file_exists($path)) {
              $size = round(filesize($path)/1024, 1) . ' Ko';
            }
            $ext = strtolower(pathinfo($f['nom_fichier'], PATHINFO_EXTENSION));
            $icon = ($ext === 'pdf') ? '📄' : (($ext === 'jpg'||$ext==='jpeg'||$ext==='png') ? '🖼️' : '📎');
          ?>
          <li style="list-style:none;">
            <a href="<?php echo htmlspecialchars($f['chemin']); ?>" target="_blank" class="d-flex align-items-center gap-2">
              <span style="font-size:1.3em;"><?php echo $icon; ?></span>
              <?php echo htmlspecialchars($f['nom_fichier']); ?>
              <span class="badge bg-info ms-2"><?php echo $size; ?></span>
              <span class="badge bg-secondary ms-1 text-uppercase"><?php echo $ext; ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<form method="post" class="card p-4">
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Nouveau statut</label>
      <select name="statut" class="form-select">
        <option value="en_attente" <?php echo $demande['statut']==='en_attente'?'selected':''; ?>>En attente</option>
        <option value="en_cours_validation" <?php echo $demande['statut']==='en_cours_validation'?'selected':''; ?>>En cours de validation</option>
        <option value="validee" <?php echo $demande['statut']==='validee'?'selected':''; ?>>Validée</option>
        <option value="rejettee" <?php echo $demande['statut']==='rejettee'?'selected':''; ?>>Rejetée</option>
        <option value="traitee" <?php echo $demande['statut']==='traitee'?'selected':''; ?>>Traitée</option>
      </select>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Service</label>
      <select name="service" class="form-select">
        <option value="">-- Aucun service --</option>
        <?php foreach ($services as $s): ?>
          <option value="<?php echo htmlspecialchars($s['nom']); ?>" 
                  <?php echo (($demande['service'] ?? null) === $s['nom']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($s['nom']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
  <a href="dashboard_admin.php" class="btn btn-secondary">Retour</a>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
