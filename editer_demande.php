<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Demande.php';
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();
$message = '';
$error = '';
$demande = null;

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mes_demandes.php');
    exit;
}

$demande_id = intval($_GET['id']);

// Get demande details
$stmt = $pdo->prepare("
    SELECT d.*, t.libelle as type_libelle
    FROM demandes d
    JOIN types_besoins t ON d.type_id = t.id
    WHERE d.id = ? AND d.demandeur_id = ?
");
$stmt->execute([$demande_id, $user['id']]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if demande exists and belongs to user
if (!$demande) {
    header('Location: mes_demandes.php');
    exit;
}

// Check if request is not pending - redirect if cannot edit
if ($demande['statut'] !== 'en_attente') {
    $_SESSION['error'] = 'Cette demande ne peut pas être modifiée car elle n\'est pas en attente.';
    header('Location: voir_demande.php?id=' . $demande_id);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demande_id'])) {
    $type_id = intval($_POST['type_id']);
    $description = trim($_POST['description']);
    $urgence = $_POST['urgence'];

    // Validation
    if (empty($description)) {
        $error = 'La description ne peut pas être vide.';
    } else {
        if (Demande::update($_POST['demande_id'], $type_id, $description, $urgence)) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Demande modifiée avec succès!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            // Refresh demande data
            $stmt->execute([$demande_id, $user['id']]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Erreur lors de la modification. Veuillez réessayer.';
        }
    }
}

$types = $pdo->query("SELECT * FROM types_besoins")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-2">
                <i class="bi bi-pencil-square me-2"></i>Éditer la demande #<?php echo $demande['id']; ?>
            </h1>
            <p class="text-muted">Modifiez les détails de votre demande</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="mes_demandes.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour à mes demandes
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Informations de la demande</h5>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">

                <!-- ID et Statut -->
                <div class="col-md-6">
                    <label class="form-label"><strong>ID de la demande</strong></label>
                    <input type="text" class="form-control" value="#<?php echo $demande['id']; ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>Statut actuel</strong></label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($demande['statut']); ?>" disabled>
                </div>

                <!-- Type -->
                <div class="col-md-6">
                    <label class="form-label"><strong>Type de besoin <span class="text-danger">*</span></strong></label>
                    <select name="type_id" class="form-select" required>
                        <option value="">-- Sélectionner un type --</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo $t['id'] == $demande['type_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['libelle']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Urgence -->
                <div class="col-md-6">
                    <label class="form-label"><strong>Urgence <span class="text-danger">*</span></strong></label>
                    <select name="urgence" class="form-select" required>
                        <option value="faible" <?php echo $demande['urgence'] === 'faible' ? 'selected' : ''; ?>>Faible</option>
                        <option value="moyenne" <?php echo $demande['urgence'] === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                        <option value="urgente" <?php echo $demande['urgence'] === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="col-12">
                    <label class="form-label"><strong>Description <span class="text-danger">*</span></strong></label>
                    <textarea name="description" class="form-control" rows="6" required placeholder="Décrivez votre demande en détail..."><?php echo htmlspecialchars($demande['description']); ?></textarea>
                    <small class="form-text text-muted">Minimum 10 caractères</small>
                </div>

                <!-- Dates -->
                <div class="col-md-6">
                    <label class="form-label"><strong>Date de création</strong></label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($demande['created_at']); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>Dernière modification</strong></label>
                    <input type="text" class="form-control" value="<?php echo $demande['updated_at'] ? htmlspecialchars($demande['updated_at']) : 'Jamais modifiée'; ?>" disabled>
                </div>

                <!-- Buttons -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                        </button>
                        <a href="mes_demandes.php" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i>Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Info:</strong> Vous pouvez modifier le type, la description et l'urgence de votre demande. Les modifications seront immédiatement enregistrées.
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
