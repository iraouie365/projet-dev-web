<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Demande.php';
require_once __DIR__ . '/classes/Database.php';

$pdo = Database::getInstance();

$types = $pdo->query("SELECT * FROM types_besoins")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_id = $_POST['type_id'];
    $description = $_POST['description'];
    $urgence = $_POST['urgence'];
    
    $validateur_id = isset($user['chef_id']) ? $user['chef_id'] : null;
    
    if (!$validateur_id) {
        $message = "Erreur: Vous devez être assigné à un chef hiérarchique pour soumettre une demande.";
    } else {
        $demande_id = Demande::create($user['id'],$type_id,$description,$urgence,$validateur_id);

        if (!empty($_FILES['piece']['name'])) {
            $f = $_FILES['piece'];
            if ($f['error'] === UPLOAD_ERR_OK) {
                $allowedExt = ['pdf','jpg','jpeg','png','doc','docx','xls','xlsx'];
                $maxBytes = 5 * 1024 * 1024; 
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt)) {
                    $message = 'Type de fichier non autorisé.';
                } elseif ($f['size'] > $maxBytes) {
                    $message = 'Fichier trop volumineux (max 5MB).';
                } else {
                    $uploadDir = __DIR__ . '/uploads';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                    $safeBase = 'demande_'.$demande_id.'_'.time();
                    $newName = $safeBase . '.' . $ext;
                    $destRel = 'uploads/'.$newName;
                    $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;

                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        $stmt = $pdo->prepare("INSERT INTO pieces_jointes (demande_id,nom_fichier,chemin) VALUES (?,?,?)");
                        $stmt->execute([$demande_id, $f['name'], $destRel]);
                    } else {
                        $message = 'Erreur lors de l\'envoi du fichier.';
                    }
                }
            }
        }

        $message = "Demande créée avec succès.";
    }
}
?>
<h1 class="mb-4">Nouvelle demande</h1>

<?php if ($message): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-3">
  <div class="mb-3">
    <label class="form-label">Type de besoin</label>
    <select name="type_id" class="form-select" required>
      <?php foreach ($types as $t): ?>
        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['libelle']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Description détaillée</label>
    <textarea name="description" class="form-control" rows="4" required></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Urgence</label>
    <select name="urgence" class="form-select" required>
      <option value="faible">Faible</option>
      <option value="moyenne">Moyenne</option>
      <option value="urgente">Urgente</option>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Pièce jointe (optionnel)</label>
    <input type="file" name="piece" class="form-control">
  </div>

  <button type="submit" class="btn btn-primary">Soumettre</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
