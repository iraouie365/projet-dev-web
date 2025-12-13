<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Demande.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Notification.php';
require_once __DIR__ . '/classes/EmailHelper.php';

$pdo = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $demande_id = $_POST['demande_id'];
    $action = $_POST['action'];
    $commentaire = $_POST['commentaire'] ?? '';

    if ($action === 'escalade') {
        // Transmission au niveau supérieur (admin)
        $stmt = $pdo->prepare("UPDATE demandes SET statut='escaladee', updated_at=NOW() WHERE id=?");
        $stmt->execute([$demande_id]);

        // Enregistrer l'escalade dans la table validation
        $stmt = $pdo->prepare("
            INSERT INTO validation (demande_id,validateur_id,action,commentaire)
            VALUES (?,?,?,?)
        ");
        $stmt->execute([$demande_id, $user['id'], 'escalade', $commentaire]);

        // Notifier tous les admins
        $admins = $pdo->query("SELECT id, email, nom FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($admins as $admin) {
            $msg = "La demande #{$demande_id} a été transmise par le validateur " . $user['nom'] . " pour traitement.";
            if ($commentaire) $msg .= " Motif: " . $commentaire;
            Notification::create($admin['id'], $msg);
        }

        // Notifier le demandeur
        $q = $pdo->prepare("SELECT d.demandeur_id, u.email, u.nom FROM demandes d JOIN users u ON d.demandeur_id = u.id WHERE d.id = ?");
        $q->execute([$demande_id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $msg = "Votre demande #{$demande_id} a été transmise au niveau supérieur pour traitement.";
            if ($commentaire) $msg .= " Motif: " . $commentaire;
            Notification::create($row['demandeur_id'], $msg);
        }
    } else {
        // Validation ou rejet normal
        $statut = ($action === 'valide') ? 'validee' : 'rejettee';
        $stmt = $pdo->prepare("UPDATE demandes SET statut=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$statut, $demande_id]);

        $stmt = $pdo->prepare("
            INSERT INTO validation (demande_id,validateur_id,action,commentaire)
            VALUES (?,?,?,?)
        ");
        $stmt->execute([$demande_id,$user['id'],$action,$commentaire]);
        // Notify the demandeur about the decision
        $q = $pdo->prepare("SELECT d.demandeur_id, u.email, u.nom FROM demandes d JOIN users u ON d.demandeur_id = u.id WHERE d.id = ?");
        $q->execute([$demande_id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $demandeur_id = $row['demandeur_id'];
            $msg = ($action === 'valide') ? "Votre demande #{$demande_id} a été validée." : "Votre demande #{$demande_id} a été rejetée.";
            if ($commentaire) $msg .= " Commentaire: " . $commentaire;
            Notification::create($demandeur_id, $msg);
            // Try to send email (will silently skip if not configured)
            EmailHelper::notifyValidation($row['email'], $row['nom'], $demande_id, $action, $commentaire);
        }
    }
}

$demandes = Demande::findForValidateur($user['id']);

// Filtering
$filterStatut = $_GET['statut'] ?? '';
$filterDemandeur = $_GET['demandeur'] ?? '';
$filterDate = $_GET['date'] ?? '';

if ($filterStatut || $filterDemandeur || $filterDate) {
    $demandes = array_filter($demandes, function($d) use ($filterStatut, $filterDemandeur, $filterDate) {
        if ($filterStatut && $d['statut'] !== $filterStatut) return false;
        if ($filterDemandeur && strpos(strtolower($d['demandeur_nom']), strtolower($filterDemandeur)) === false) return false;
        if ($filterDate && !str_starts_with($d['created_at'], $filterDate)) return false;
        return true;
    });
}
?>
<h1 class="mb-4">Demandes de mon équipe</h1>

<?php
// Summary for validateur
$teamPending = 0;
$teamTotal = count($demandes);
foreach ($demandes as $dd) { if ($dd['statut'] === 'en_attente') $teamPending++; }
?>

<div class="hero-section">
  <h2>Bonjour <?php echo htmlspecialchars($user['nom']); ?> — Espace validateur</h2>
  <p class="small-stat"><?php echo $teamTotal; ?> demandes dans votre équipe • <?php echo $teamPending; ?> en attente</p>
  <div class="hero-actions">
    <a href="dashboard_validateur.php" class="btn btn-light">Rafraîchir</a>
    <a href="notifications.php" class="btn btn-outline-light">Notifications</a>
  </div>
</div>

<div class="mb-3">
  <form method="get" class="row g-2">
    <div class="col-md-3">
      <select name="statut" class="form-select form-select-sm">
        <option value="">-- Tous les statuts --</option>
        <option value="en_attente" <?php echo $filterStatut === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
        <option value="en_cours_validation" <?php echo $filterStatut === 'en_cours_validation' ? 'selected' : ''; ?>>En cours</option>
        <option value="validee" <?php echo $filterStatut === 'validee' ? 'selected' : ''; ?>>Validée</option>
        <option value="rejettee" <?php echo $filterStatut === 'rejettee' ? 'selected' : ''; ?>>Rejetée</option>
        <option value="escaladee" <?php echo $filterStatut === 'escaladee' ? 'selected' : ''; ?>>Transmise</option>
      </select>
    </div>
    <div class="col-md-3">
      <input type="text" name="demandeur" class="form-control form-control-sm" placeholder="Nom demandeur" value="<?php echo htmlspecialchars($filterDemandeur); ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterDate); ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-sm btn-primary w-100" type="submit">Filtrer</button>
    </div>
    <div class="col-md-2">
      <a href="dashboard_validateur.php" class="btn btn-sm btn-secondary w-100">Réinitialiser</a>
    </div>
  </form>
</div>


<table class="table table-bordered">
  <thead>
    <tr>
      <th>#</th><th>Demandeur</th><th>Type</th><th>Urgence</th><th>Description</th><th>Statut</th><th>Pièce(s) jointe(s)</th><th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php if (empty($demandes)): ?>
    <tr><td colspan="8" class="text-center text-muted">Aucune demande.</td></tr>
  <?php else: ?>
  <?php foreach ($demandes as $d): ?>
    <tr>
      <td><?php echo $d['id']; ?></td>
      <td><?php echo htmlspecialchars($d['demandeur_nom']); ?></td>
      <td><?php echo htmlspecialchars($d['type_libelle']); ?></td>
      <td><?php echo htmlspecialchars($d['urgence']); ?></td>
      <td style="max-width: 300px; white-space: normal; word-wrap: break-word;">
        <?php echo htmlspecialchars(substr($d['description'], 0, 100)); ?>
        <?php if (strlen($d['description']) > 100): ?>
          ...
        <?php endif; ?>
      </td>
      <td>
        <span class="badge 
          <?php 
            if ($d['statut'] === 'validee') echo 'bg-success';
            elseif ($d['statut'] === 'rejettee') echo 'bg-danger';
            elseif ($d['statut'] === 'en_attente') echo 'bg-warning';
            elseif ($d['statut'] === 'escaladee') echo 'bg-info';
            else echo 'bg-secondary';
          ?>">
          <?php 
            if ($d['statut'] === 'escaladee') echo 'Transmise';
            else echo htmlspecialchars($d['statut']); 
          ?>
        </span>
      </td>
      <td>
        <?php
        // Fetch files for this demande
        $files = [];
        $stmt = $pdo->prepare("SELECT * FROM pieces_jointes WHERE demande_id = ?");
        $stmt->execute([$d['id']]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if (!empty($files)): ?>
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
              <li style="list-style: none;">
                <a href="<?php echo htmlspecialchars($f['chemin']); ?>" target="_blank" class="d-flex align-items-center gap-2">
                  <span style="font-size:1.3em;"><?php echo $icon; ?></span>
                  <?php echo htmlspecialchars($f['nom_fichier']); ?>
                  <span class="badge bg-info ms-2"><?php echo $size; ?></span>
                  <span class="badge bg-secondary ms-1 text-uppercase"><?php echo $ext; ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <span class="text-muted">-</span>
        <?php endif; ?>
      </td>
      <td>
        <?php if (in_array($d['statut'], ['en_attente','en_cours_validation'])): ?>
          <form method="post">
            <input type="hidden" name="demande_id" value="<?php echo $d['id']; ?>">
            <textarea name="commentaire" class="form-control form-control-sm mb-2" placeholder="Commentaire..." rows="3"></textarea>
            <div class="d-flex gap-2 flex-wrap">
              <button name="action" value="valide" class="btn btn-sm btn-success flex-grow-1">✓ Valider</button>
              <button name="action" value="rejete" class="btn btn-sm btn-danger flex-grow-1">✕ Rejeter</button>
              <button name="action" value="escalade" class="btn btn-sm btn-info flex-grow-1" title="Transmettre au niveau supérieur (Admin)">⬆ Transmettre</button>
            </div>
          </form>
        <?php elseif ($d['statut'] === 'escaladee'): ?>
          <span class="badge bg-info">Transmise à l'admin</span>
        <?php else: ?>
          <span class="text-muted text-sm">Clôturée</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
