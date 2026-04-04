<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];
require_once __DIR__ . '/../classes/Notification.php';
$unreadCount = Notification::countUnread($user['id']);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion des Besoins</title>
  <link href="assets/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/bootstrap-icons-1.11.1/bootstrap-icons.css">
  <link href="assets/custom.css" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark mb-0">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo ($user['role'] === 'demandeur' ? 'dashboard_demandeur.php' : ($user['role'] === 'validateur' ? 'dashboard_validateur.php' : 'dashboard_admin.php')); ?>">
      <div class="logo-wrapper">
        <i class="bi bi-clipboard-check-fill"></i>
      </div>
      <span class="brand-text">Gestion des Besoins</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <?php if ($user['role'] === 'demandeur'): ?>
          <li class="nav-item"><a class="nav-link" href="dashboard_demandeur.php"><i class="bi bi-speedometer2 me-1"></i>Tableau de bord</a></li>
          <li class="nav-item"><a class="nav-link" href="nouvelle_demande.php"><i class="bi bi-plus-circle me-1"></i>Nouvelle demande</a></li>
          <li class="nav-item"><a class="nav-link" href="mes_demandes.php"><i class="bi bi-file-earmark-text me-1"></i>Mes demandes</a></li>
        <?php elseif ($user['role'] === 'validateur'): ?>
          <li class="nav-item"><a class="nav-link" href="dashboard_validateur.php"><i class="bi bi-clipboard-check me-1"></i>Demandes à valider</a></li>
        <?php elseif ($user['role'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-1"></i>Toutes les demandes</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_users.php"><i class="bi bi-people me-1"></i>Utilisateurs</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_types.php"><i class="bi bi-tags me-1"></i>Types de besoins</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_services.php"><i class="bi bi-briefcase me-1"></i>Services</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="changer_mot_de_passe.php"><i class="bi bi-shield-lock me-1"></i>Mot de passe</a></li>
      </ul>
      <a class="nav-link text-white me-3 position-relative notification-link" href="notifications.php">
        <i class="bi bi-bell-fill"></i>
        <?php if ($unreadCount > 0): ?>
          <span class="notification-badge"><?php echo $unreadCount; ?></span>
        <?php endif; ?>
      </a>
      <div class="user-info me-3">
        <i class="bi bi-person-circle me-1"></i>
        <span><?php echo htmlspecialchars($user['nom']); ?></span>
        <span class="role-badge"><?php echo $user['role']; ?></span>
      </div>
      <a class="btn btn-logout" href="logout.php">
        <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
      </a>
    </div>
  </div>
</nav>
<div class="container">
