<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Demande.php';

$demandes = Demande::all();

$myRequests = Demande::findByDemandeur($user['id']);

$totalCount = count($demandes);
$myCount = count($myRequests);

$myOpen = 0;
foreach ($myRequests as $r) {
    if (!in_array($r['statut'], ['traitee', 'rejettee'])) $myOpen++;
}
?>
<h1 class="mb-4">Toutes les demandes</h1>

<a href="nouvelle_demande.php" class="btn btn-primary mb-3">Créer une nouvelle demande</a>

<div class="hero-section">
  <h2>Bonjour <?php echo htmlspecialchars($user['nom']); ?> — Vue globale</h2>
  <p class="small-stat"><?php echo $totalCount; ?> demandes totales • Vous avez <?php echo $myCount; ?> demandes (<?php echo $myOpen; ?> en cours)</p>
  <div class="hero-actions">
    <a href="nouvelle_demande.php" class="btn btn-light">Créer une demande</a>
    <a href="mes_demandes.php" class="btn btn-outline-light">Mes demandes</a>
  </div>
</div>

<table class="table table-striped table-lg">
  <thead>
    <tr>
      <th>#</th><th>Demandeur</th><th>Type</th><th>Urgence</th><th>Statut</th><th>Créée le</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($demandes as $d): ?>
    <tr>
      <td><?php echo $d['id']; ?></td>
      <td><?php echo htmlspecialchars($d['demandeur_nom'] ?? ''); ?></td>
      <td><?php echo htmlspecialchars($d['type_libelle'] ?? ''); ?></td>
      <td><?php echo htmlspecialchars($d['urgence']); ?></td>
      <td><span class="badge bg-secondary"><?php echo htmlspecialchars($d['statut']); ?></span></td>
      <td><?php echo $d['created_at']; ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
