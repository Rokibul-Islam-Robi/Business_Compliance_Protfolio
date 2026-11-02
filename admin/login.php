<?php
/** admin/login.php - sign-in form. */
define('ADMIN_PUBLIC', true);
require __DIR__ . '/bootstrap.php';

if (Auth::check()) {
    redirect('index.php');
}

$error = null;
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::require();
    // Sanitize: trim + cap length (passwords are never altered)
    $login    = mb_substr(trim(strip_tags((string) ($_POST['username'] ?? ''))), 0, 150);
    $password = (string) ($_POST['password'] ?? '');

    if ($login === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $error = Auth::attempt($login, $password);
        if ($error === null) {
            redirect('index.php');
        }
    }
}
$flash = Flash::pull();
$siteName = 'DevFolio';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Sign in · <?= e($siteName) ?> CMS</title>
  <link rel="icon" href="../img/favicon.png">
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/admin.css" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <div class="card login-card">
    <div class="card-body p-4 p-sm-5">
      <div class="text-center mb-4">
        <div class="display-5 text-primary"><i class="bi bi-layers-half"></i></div>
        <h1 class="h4 mt-2 mb-1"><?= e($siteName) ?> CMS</h1>
        <p class="text-muted small mb-0">Sign in to manage your portfolio</p>
      </div>

      <?php foreach ($flash as $f): ?><div class="alert alert-<?= e($f['type']) ?> py-2 small"><?= e($f['message']) ?></div><?php endforeach; ?>
      <?php if ($error): ?><div class="alert alert-danger py-2 small"><i class="bi bi-x-octagon me-1"></i><?= e($error) ?></div><?php endif; ?>

      <form method="post" autocomplete="off" novalidate>
        <?= Csrf::field() ?>
        <div class="mb-3">
          <label class="form-label" for="username">Username or email</label>
          <div class="input-group"><span class="input-group-text"><i class="bi bi-person"></i></span>
            <input class="form-control" id="username" name="username" value="<?= e($login) ?>" maxlength="150" required autofocus></div>
        </div>
        <div class="mb-4">
          <label class="form-label" for="password">Password</label>
          <div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input class="form-control" type="password" id="password" name="password" required></div>
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i>Sign in</button>
      </form>
      <p class="text-center mt-4 mb-0 small"><a href="../index.php" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Back to website</a></p>
    </div>
  </div>
</div>
</body>
</html>
