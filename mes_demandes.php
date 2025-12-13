<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Demande.php';
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();
$demandes = Demande::findByDemandeur($user['id']);
?>
<h1 class="mb-4">Mes demandes</h1>

<table class="table table-hover">
  <thead>
    <tr>
      <th>#</th><th>Type</th><th>Urgence</th><th>Statut</th><th>Pièce(s) jointe(s)</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($demandes as $d): ?>
    <tr>
      <td><?php echo $d['id']; ?></td>
      <td><?php echo htmlspecialchars($d['type_libelle']); ?></td>
      <td><?php echo htmlspecialchars($d['urgence']); ?></td>
      <td><?php echo htmlspecialchars($d['statut']); ?></td>
      <td>
        <?php
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
        <a href="voir_demande.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-eye"></i> Voir
        </a>
        <?php if ($d['statut'] === 'en_attente'): ?>
          <a href="editer_demande.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-pencil"></i> Éditer
          </a>
        <?php else: ?>
          <button class="btn btn-sm btn-outline-secondary" disabled title="Seules les demandes en attente peuvent être modifiées">
            <i class="bi bi-pencil"></i> Éditer
          </button>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
