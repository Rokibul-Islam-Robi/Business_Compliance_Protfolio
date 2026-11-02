<?php
/** admin/index.php - dashboard with metric cards and recent messages. */
require __DIR__ . '/bootstrap.php';

$count = static fn(string $sql): int => (int) Database::fetchValue($sql);

$metrics = [
  ['Projects',        $count('SELECT COUNT(*) FROM portfolio_items'),    'bi-images',        'bg-grad-1', 'projects.php'],
  ['Services',        $count('SELECT COUNT(*) FROM services'),           'bi-briefcase',     'bg-grad-2', 'services.php'],
  ['Skills',          $count('SELECT COUNT(*) FROM skills'),             'bi-sliders',       'bg-grad-4', 'about.php'],
  ['Testimonials',    $count('SELECT COUNT(*) FROM testimonials'),       'bi-chat-quote',    'bg-grad-3', 'testimonials.php'],
  ['Unread messages', $count('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'), 'bi-envelope-exclamation', 'bg-grad-5', 'messages.php?status=unread'],
  ['Total messages',  $count('SELECT COUNT(*) FROM contact_messages'),   'bi-envelope-open', 'bg-grad-6', 'messages.php'],
];

$recent = Database::fetchAll('SELECT id, name, email, subject, is_read, created_at FROM contact_messages ORDER BY created_at DESC, id DESC LIMIT 6');

$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';
require __DIR__ . '/partials/header.php';
?>
<?php if (Auth::usingDefaultPassword()): ?>
  <div class="alert alert-warning shadow-sm d-flex align-items-center flex-wrap gap-2">
    <i class="bi bi-shield-exclamation fs-5"></i>
    <div class="flex-grow-1"><strong>Security notice:</strong> you are still using the default admin password. Please change it now.</div>
    <a href="account.php" class="btn btn-warning btn-sm">Change password</a>
  </div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <?php foreach ($metrics as [$label, $num, $icon, $grad, $link]): ?>
    <div class="col-6 col-xl-4 col-xxl-2">
      <a href="<?= e($link) ?>" class="metric">
        <span class="ico <?= e($grad) ?>"><i class="bi <?= e($icon) ?>"></i></span>
        <span><span class="num d-block"><?= $num ?></span><span class="lbl"><?= e($label) ?></span></span>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="card admin-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0"><i class="bi bi-envelope me-2"></i>Recent messages</h2>
        <a href="messages.php" class="btn btn-sm btn-outline-primary">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 admin-table">
          <thead><tr><th>From</th><th>Subject</th><th>Received</th><th></th></tr></thead>
          <tbody>
          <?php if (!$recent): ?><tr><td colspan="4" class="text-center text-muted py-4">No messages yet.</td></tr><?php endif; ?>
          <?php foreach ($recent as $m): ?>
            <tr class="<?= $m['is_read'] ? '' : 'unread' ?>">
              <td><?= e($m['name']) ?><div class="small text-muted fw-normal"><?= e($m['email']) ?></div></td>
              <td><?= e(truncate($m['subject'], 45)) ?></td>
              <td class="text-nowrap small"><?= e(date('d M Y, H:i', strtotime($m['created_at']))) ?></td>
              <td><?= $m['is_read'] ? '' : '<span class="badge text-bg-primary">New</span>' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card admin-card">
      <div class="card-header"><h2 class="h6 mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick actions</h2></div>
      <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action" href="hero.php"><i class="bi bi-stars me-2 text-primary"></i>Edit hero section</a>
        <a class="list-group-item list-group-item-action" href="projects.php"><i class="bi bi-plus-circle me-2 text-primary"></i>Add a project</a>
        <a class="list-group-item list-group-item-action" href="services.php"><i class="bi bi-briefcase me-2 text-primary"></i>Manage services</a>
        <a class="list-group-item list-group-item-action" href="contact.php"><i class="bi bi-geo-alt me-2 text-primary"></i>Update contact details</a>
        <a class="list-group-item list-group-item-action" href="../index.php" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-2 text-primary"></i>Open public website</a>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
