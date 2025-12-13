<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Notification.php';

$notes = Notification::findByUser($user['id']);
Notification::markAllRead($user['id']);
?>
<h1 class="mb-4">Notifications</h1>

<?php if (empty($notes)): ?>
  <div class="alert alert-info">Aucune notification.</div>
<?php else: ?>
  <ul class="list-group">
    <?php foreach ($notes as $n): ?>
      <li class="list-group-item <?php echo $n['is_read'] ? '' : 'list-group-item-warning'; ?>">
        <div><?php echo nl2br(htmlspecialchars($n['message'])); ?></div>
        <small class="text-muted"><?php echo $n['created_at']; ?></small>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
