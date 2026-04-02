<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Demande.php';

$demandes = Demande::all();

// Filtering
$filterStatut = $_GET['statut'] ?? '';
$filterDemandeur = $_GET['demandeur'] ?? '';
$filterService = $_GET['service'] ?? '';
$filterDate = $_GET['date'] ?? '';

if ($filterStatut || $filterDemandeur || $filterService || $filterDate) {
    $demandes = array_filter($demandes, function($d) use ($filterStatut, $filterDemandeur, $filterService, $filterDate) {
        if ($filterStatut && $d['statut'] !== $filterStatut) return false;
        if ($filterDemandeur && strpos(strtolower($d['demandeur_nom']), strtolower($filterDemandeur)) === false) return false;
    $service = $d['service'] ?? '';
    if ($filterService && ($service === '' || strpos(strtolower($service), strtolower($filterService)) === false)) return false;
        if ($filterDate && !str_starts_with($d['created_at'], $filterDate)) return false;
        return true;
    });
}
?>
<h1 class="mb-4">Toutes les demandes</h1>

<?php
// Small summary metrics for hero
$pendingCount = 0;
foreach ($demandes as $dd) { if ($dd['statut'] === 'en_attente') $pendingCount++; }
$totalCount = count($demandes);
?>

<div class="hero-section">
  <h2>Bonjour <?php echo htmlspecialchars($user['nom']); ?> — Espace administrateur</h2>
  <p class="small-stat"><?php echo $totalCount; ?> demandes totales • <?php echo $pendingCount; ?> en attente</p>
  <div class="hero-actions">
    <a href="nouvelle_demande.php" class="btn btn-light">Créer une demande</a>
    <a href="admin_users.php" class="btn btn-outline-light">Gérer les utilisateurs</a>
  </div>
</div>

<div class="mb-3">
  <form method="get" class="row g-2">
    <div class="col-md-2">
      <select name="statut" class="form-select form-select-sm">
        <option value="">-- Tous les statuts --</option>
        <option value="en_attente" <?php echo $filterStatut === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
        <option value="en_cours_validation" <?php echo $filterStatut === 'en_cours_validation' ? 'selected' : ''; ?>>En cours</option>
        <option value="validee" <?php echo $filterStatut === 'validee' ? 'selected' : ''; ?>>Validée</option>
        <option value="rejettee" <?php echo $filterStatut === 'rejettee' ? 'selected' : ''; ?>>Rejetée</option>
        <option value="traitee" <?php echo $filterStatut === 'traitee' ? 'selected' : ''; ?>>Traitée</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="text" name="demandeur" class="form-control form-control-sm" placeholder="Demandeur" value="<?php echo htmlspecialchars($filterDemandeur); ?>">
    </div>
    <div class="col-md-2">
      <input type="text" name="service" class="form-control form-control-sm" placeholder="Service" value="<?php echo htmlspecialchars($filterService); ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterDate); ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-sm btn-primary w-100" type="submit">Filtrer</button>
    </div>
    <div class="col-md-2">
      <a href="dashboard_admin.php" class="btn btn-sm btn-secondary w-100">Réinitialiser</a>
    </div>
  </form>
</div>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>#</th><th>Demandeur</th><th>Type</th><th>Urgence</th><th>Statut</th><th>Service</th><th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php if (empty($demandes)): ?>
    <tr><td colspan="7" class="text-center text-muted">Aucune demande.</td></tr>
  <?php else: ?>
  <?php foreach ($demandes as $d): ?>
    <tr>
      <td><?php echo $d['id']; ?></td>
      <td><?php echo htmlspecialchars($d['demandeur_nom']); ?></td>
      <td><?php echo htmlspecialchars($d['type_libelle']); ?></td>
      <td><?php echo htmlspecialchars($d['urgence']); ?></td>
      <td>
        <span class="badge 
          <?php 
            if ($d['statut'] === 'traitee') echo 'bg-success';
            elseif ($d['statut'] === 'rejettee') echo 'bg-danger';
            elseif ($d['statut'] === 'validee') echo 'bg-info';
            elseif ($d['statut'] === 'en_cours_validation') echo 'bg-warning';
            else echo 'bg-secondary';
          ?>">
          <?php echo htmlspecialchars($d['statut']); ?>
        </span>
      </td>
      <td><?php echo !empty($d['service']) ? '<code>' . htmlspecialchars($d['service']) . '</code>' : '<em class="text-muted">-</em>'; ?></td>
      <td>
        <a href="traiter_demande.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-primary">
          Traiter
        </a>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
