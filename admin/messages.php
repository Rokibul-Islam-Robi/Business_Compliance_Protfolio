<?php
/** admin/messages.php - inbox for the public contact form (view, mark read/unread, delete). */
require __DIR__ . '/bootstrap.php';

const PER_PAGE = 15;

// ---------- actions (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::require();
    $action = (string) ($_POST['_action'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($action === 'mark_all_read') {
        Database::execute('UPDATE contact_messages SET is_read = 1 WHERE is_read = 0');
        Flash::add('success', 'All messages marked as read.');
    } elseif ($id !== false && $action === 'toggle') {
        Database::execute('UPDATE contact_messages SET is_read = 1 - is_read WHERE id = :id', [':id' => $id]);
        Flash::add('success', 'Message status updated.');
    } elseif ($id !== false && $action === 'delete') {
        Database::execute('DELETE FROM contact_messages WHERE id = :id', [':id' => $id]);
        Flash::add('success', 'Message deleted.');
    }
    // return to the same filter/page
    $qs = [];
    $st = (string) ($_POST['status'] ?? '');
    if (in_array($st, ['unread', 'read'], true)) { $qs['status'] = $st; }
    $pg = (int) ($_POST['page'] ?? 1);
    if ($pg > 1) { $qs['page'] = $pg; }
    redirect('messages.php' . ($qs ? '?' . http_build_query($qs) : ''));
}

// ---------- listing (GET) ----------
$status = (string) ($_GET['status'] ?? 'all');
if (!in_array($status, ['all', 'unread', 'read'], true)) { $status = 'all'; }
$where = match ($status) { 'unread' => 'WHERE is_read = 0', 'read' => 'WHERE is_read = 1', default => '' };

$total = (int) Database::fetchValue("SELECT COUNT(*) FROM contact_messages $where");
$pages = max(1, (int) ceil($total / PER_PAGE));
$page  = max(1, min($pages, (int) ($_GET['page'] ?? 1)));
$offset = ($page - 1) * PER_PAGE;      // ints only => safe to inline
$rows = Database::fetchAll("SELECT * FROM contact_messages $where ORDER BY created_at DESC, id DESC LIMIT " . PER_PAGE . " OFFSET $offset");

$counts = [
    'all'    => (int) Database::fetchValue('SELECT COUNT(*) FROM contact_messages'),
    'unread' => (int) Database::fetchValue('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'),
];
$counts['read'] = $counts['all'] - $counts['unread'];

$pageTitle = 'Messages';
$activeMenu = 'messages';
require __DIR__ . '/partials/header.php';

/** hidden fields that return the user to the current list state */
$ctx = '<input type="hidden" name="status" value="' . e($status) . '"><input type="hidden" name="page" value="' . $page . '">';
?>
<div class="card admin-card">
  <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
    <ul class="nav nav-pills small">
      <?php foreach (['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $k => $label): ?>
        <li class="nav-item"><a class="nav-link py-1<?= $status === $k ? ' active' : '' ?>" href="messages.php<?= $k === 'all' ? '' : '?status=' . $k ?>"><?= $label ?> <span class="badge text-bg-light"><?= $counts[$k] ?></span></a></li>
      <?php endforeach; ?>
    </ul>
    <?php if ($counts['unread'] > 0): ?>
      <form method="post" class="m-0"><?= Csrf::field() ?><input type="hidden" name="_action" value="mark_all_read">
        <button class="btn btn-outline-primary btn-sm"><i class="bi bi-check2-all me-1"></i>Mark all as read</button></form>
    <?php endif; ?>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 admin-table">
      <thead><tr><th>From</th><th>Subject</th><th class="d-none d-md-table-cell">Received</th><th class="text-end" style="width:150px">Actions</th></tr></thead>
      <tbody>
      <?php if (!$rows): ?><tr><td colspan="4" class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-1"></i>No messages found.</td></tr><?php endif; ?>
      <?php foreach ($rows as $m): ?>
        <tr class="<?= $m['is_read'] ? '' : 'unread' ?>">
          <td><?= $m['is_read'] ? '' : '<i class="bi bi-circle-fill text-primary me-1" style="font-size:.5rem"></i>' ?><?= e($m['name']) ?>
              <div class="small text-muted fw-normal"><?= e($m['email']) ?></div></td>
          <td><?= e(truncate($m['subject'], 50)) ?></td>
          <td class="d-none d-md-table-cell small text-nowrap fw-normal"><?= e(date('d M Y, H:i', strtotime($m['created_at']))) ?></td>
          <td class="text-end text-nowrap">
            <button class="btn btn-outline-primary btn-sm" title="Read" data-bs-toggle="modal" data-bs-target="#viewModal"
                    data-msg="<?= e(json_encode([
                        'id' => (int) $m['id'], 'name' => $m['name'], 'email' => $m['email'], 'subject' => $m['subject'],
                        'message' => $m['message'], 'date' => date('d M Y, H:i', strtotime($m['created_at'])),
                        'read' => (int) $m['is_read'],
                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"><i class="bi bi-eye"></i></button>
            <form method="post" class="d-inline"><?= Csrf::field() ?><?= $ctx ?><input type="hidden" name="_action" value="toggle"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button class="btn btn-outline-secondary btn-sm" title="<?= $m['is_read'] ? 'Mark as unread' : 'Mark as read' ?>"><i class="bi bi-<?= $m['is_read'] ? 'envelope' : 'envelope-open' ?>"></i></button></form>
            <form method="post" class="d-inline" data-confirm="Delete this message permanently?"><?= Csrf::field() ?><?= $ctx ?><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash3"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-center">
    <nav><ul class="pagination pagination-sm mb-0">
      <?php for ($i = 1; $i <= $pages; $i++):
          $q = http_build_query(array_filter(['status' => $status === 'all' ? null : $status, 'page' => $i > 1 ? $i : null])); ?>
        <li class="page-item<?= $i === $page ? ' active' : '' ?>"><a class="page-link" href="messages.php<?= $q ? '?' . e($q) : '' ?>"><?= $i ?></a></li>
      <?php endfor; ?>
    </ul></nav>
  </div>
  <?php endif; ?>
</div>

<!-- Read-message modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vm-subject"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3 small text-muted"><strong id="vm-name"></strong> &lt;<a id="vm-email" href="#"></a>&gt; · <span id="vm-date"></span></p>
        <div class="msg-body" id="vm-message"></div>
      </div>
      <div class="modal-footer justify-content-between">
        <a id="vm-reply" class="btn btn-primary" href="#"><i class="bi bi-reply me-1"></i>Reply by email</a>
        <div class="d-flex gap-2">
          <form method="post" class="m-0"><?= Csrf::field() ?><?= $ctx ?><input type="hidden" name="_action" value="toggle"><input type="hidden" name="id" value="" id="vm-id-toggle">
            <button class="btn btn-outline-secondary" id="vm-toggle-btn"></button></form>
          <form method="post" class="m-0" data-confirm="Delete this message permanently?"><?= Csrf::field() ?><?= $ctx ?><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="" id="vm-id-delete">
            <button class="btn btn-outline-danger"><i class="bi bi-trash3"></i></button></form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$pageScripts = <<<'HTML'
<script>
document.getElementById('viewModal').addEventListener('show.bs.modal', function (ev) {
  var m = JSON.parse(ev.relatedTarget.getAttribute('data-msg'));
  document.getElementById('vm-subject').textContent = m.subject;
  document.getElementById('vm-name').textContent = m.name;
  var a = document.getElementById('vm-email'); a.textContent = m.email; a.href = 'mailto:' + m.email;
  document.getElementById('vm-date').textContent = m.date;
  document.getElementById('vm-message').textContent = m.message;
  document.getElementById('vm-reply').href = 'mailto:' + m.email + '?subject=' + encodeURIComponent('Re: ' + m.subject);
  document.getElementById('vm-id-toggle').value = m.id;
  document.getElementById('vm-id-delete').value = m.id;
  document.getElementById('vm-toggle-btn').innerHTML = m.read
    ? '<i class="bi bi-envelope me-1"></i>Mark as unread'
    : '<i class="bi bi-envelope-open me-1"></i>Mark as read';
});
</script>
HTML;
require __DIR__ . '/partials/footer.php';
