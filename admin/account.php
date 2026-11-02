<?php
/** admin/account.php - change username/email and password. */
require __DIR__ . '/bootstrap.php';

$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::require();
    $action = (string) ($_POST['_action'] ?? '');

    if ($action === 'profile') {
        $username = trim(strip_tags((string) ($_POST['username'] ?? '')));
        $email    = trim(strip_tags((string) ($_POST['email'] ?? '')));
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            Flash::add('danger', 'Username must be 3–50 characters (letters, numbers, dot, dash, underscore).');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            Flash::add('danger', 'Please enter a valid email address.');
        } else {
            try {
                Database::execute('UPDATE admin_users SET username = :u, email = :e WHERE id = :id',
                    [':u' => $username, ':e' => $email, ':id' => $user['id']]);
                Flash::add('success', 'Profile updated.');
            } catch (PDOException $e) {
                Flash::add('danger', 'That username or email is already in use.');
            }
        }
    } elseif ($action === 'password') {
        $err = Auth::changePassword(
            (int) $user['id'],
            (string) ($_POST['current_password'] ?? ''),
            (string) ($_POST['new_password'] ?? ''),
            (string) ($_POST['confirm_password'] ?? '')
        );
        Flash::add($err ? 'danger' : 'success', $err ?? 'Password changed successfully.');
    }
    redirect('account.php');
}

$pageTitle = 'My Account';
$activeMenu = 'account';
require __DIR__ . '/partials/header.php';
?>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card admin-card h-100">
      <div class="card-header"><h2 class="h6 mb-0"><i class="bi bi-person me-2"></i>Profile</h2></div>
      <div class="card-body">
        <form method="post" autocomplete="off">
          <?= Csrf::field() ?><input type="hidden" name="_action" value="profile">
          <div class="mb-3"><label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" value="<?= e($user['username']) ?>" maxlength="50" required></div>
          <div class="mb-3"><label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= e($user['email']) ?>" maxlength="150" required></div>
          <p class="small text-muted">Last sign-in: <?= $user['last_login_at'] ? e(date('d M Y, H:i', strtotime($user['last_login_at']))) : '—' ?></p>
          <button class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save profile</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card admin-card h-100">
      <div class="card-header"><h2 class="h6 mb-0"><i class="bi bi-key me-2"></i>Change password</h2></div>
      <div class="card-body">
        <form method="post" autocomplete="off">
          <?= Csrf::field() ?><input type="hidden" name="_action" value="password">
          <div class="mb-3"><label class="form-label" for="cp">Current password</label>
            <input class="form-control" type="password" id="cp" name="current_password" required autocomplete="current-password"></div>
          <div class="mb-3"><label class="form-label" for="np">New password</label>
            <input class="form-control" type="password" id="np" name="new_password" minlength="8" required autocomplete="new-password">
            <div class="form-text">At least 8 characters.</div></div>
          <div class="mb-3"><label class="form-label" for="cf">Confirm new password</label>
            <input class="form-control" type="password" id="cf" name="confirm_password" minlength="8" required autocomplete="new-password"></div>
          <button class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Update password</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
