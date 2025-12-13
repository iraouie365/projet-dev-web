<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Demande.php';
require_once __DIR__ . '/classes/Database.php';

// Check if id is provided
if (!isset($_GET['id'])) {
    header('Location: mes_demandes.php');
    exit;
}

$pdo = Database::getInstance();
$demande_id = intval($_GET['id']);

// Get the request details
$stmt = $pdo->prepare("
    SELECT d.*, t.libelle as type_libelle
    FROM demandes d
    JOIN types_besoins t ON d.type_id = t.id
    WHERE d.id = ?
");
$stmt->execute([$demande_id]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify the demande exists and belongs to the current user
if (!$demande || $demande['demandeur_id'] !== $user['id']) {
    header('Location: mes_demandes.php');
    exit;
}

// Get attached files
$files_stmt = $pdo->prepare("SELECT * FROM pieces_jointes WHERE demande_id = ?");
$files_stmt->execute([$demande_id]);
$files = $files_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-lg mt-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-eye-fill me-2"></i>Détails de la demande #<?php echo htmlspecialchars($demande['id']); ?>
                    </h5>
                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($demande['statut']); ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Type de besoin</label>
                            <p class="fs-5 fw-semibold text-dark"><?php echo htmlspecialchars($demande['type_libelle']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Urgence</label>
                            <p class="fs-5 fw-semibold">
                                <?php
                                $urgence = htmlspecialchars($demande['urgence']);
                                if ($urgence === 'urgente') {
                                    echo '<span class="badge bg-danger">Urgente</span>';
                                } elseif ($urgence === 'moyenne') {
                                    echo '<span class="badge bg-warning">Moyenne</span>';
                                } else {
                                    echo '<span class="badge bg-success">Faible</span>';
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small">Description</label>
                        <div class="p-3 bg-light rounded border" style="white-space: pre-wrap; line-height: 1.6;">
                            <?php echo htmlspecialchars($demande['description']); ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Créée le</label>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar me-2"></i><?php echo htmlspecialchars($demande['created_at']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Dernière modification</label>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar me-2"></i><?php echo htmlspecialchars($demande['updated_at']); ?>
                            </p>
                        </div>
                    </div>

                    <?php if (!empty($files)): ?>
                        <div class="mb-4">
                            <label class="form-label text-muted small">Pièce(s) jointe(s)</label>
                            <div class="list-group">
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
                                    <a href="<?php echo htmlspecialchars($f['chemin']); ?>" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <span style="font-size:1.5em;"><?php echo $icon; ?></span>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($f['nom_fichier']); ?></h6>
                                                <small class="text-muted"><?php echo $size; ?></small>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge bg-secondary"><?php echo strtoupper($ext); ?></span>
                                            <i class="bi bi-download ms-2"></i>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light d-flex gap-2 justify-content-between">
                    <a href="mes_demandes.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                    <a href="editer_demande.php?id=<?php echo htmlspecialchars($demande['id']); ?>" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i>Éditer
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted small">ID de la demande</span>
                        <p class="fw-semibold">#<?php echo htmlspecialchars($demande['id']); ?></p>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small">Statut actuel</span>
                        <p>
                            <?php
                            $statut = htmlspecialchars($demande['statut']);
                            if ($statut === 'Validée') {
                                echo '<span class="badge bg-success">Validée</span>';
                            } elseif ($statut === 'Rejetée') {
                                echo '<span class="badge bg-danger">Rejetée</span>';
                            } elseif ($statut === 'En attente') {
                                echo '<span class="badge bg-warning text-dark">En attente</span>';
                            } else {
                                echo '<span class="badge bg-secondary">' . $statut . '</span>';
                            }
                            ?>
                        </p>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            Créée <?php echo date('d/m/Y à H:i', strtotime($demande['created_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>

            <?php if (!empty($files)): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i>Fichiers (<?php echo count($files); ?>)</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($files as $f): ?>
                                <?php
                                $ext = strtolower(pathinfo($f['nom_fichier'], PATHINFO_EXTENSION));
                                $icon = ($ext === 'pdf') ? '📄' : (($ext === 'jpg'||$ext==='jpeg'||$ext==='png') ? '🖼️' : '📎');
                                ?>
                                <small>
                                    <span style="font-size:1.2em;"><?php echo $icon; ?></span>
                                    <?php echo htmlspecialchars(substr($f['nom_fichier'], 0, 20)) . (strlen($f['nom_fichier']) > 20 ? '...' : ''); ?>
                                </small>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
