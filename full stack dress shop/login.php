<?php
// login.php — DRIP ZONE Admin Login
session_start();
require_once 'db.php';

if (!empty($_SESSION['admin_id'])) { header('Location: admin.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id,password,full_name FROM Admin_Users WHERE username=?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Wrong credentials. Try again!';
        }
    } else {
        $error = 'Enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin Login — DRIP ZONE 🔥</title>
  <meta name="robots" content="noindex,nofollow"/>
  <link rel="stylesheet" href="style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
</head>
<body>
<main class="login-page" id="login-page">
  <div class="login-card">

    <div class="login-logo">
      <span class="login-logo-icon" aria-hidden="true">🔥</span>
      <div class="login-logo-text">DRIP ZONE</div>
      <div class="login-logo-sub">Admin Panel — Managers Only</div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger" role="alert">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" id="login-form" novalidate>
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input type="text" id="username" name="username" class="form-control"
               placeholder="admin" autocomplete="username" required
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative;">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="••••••••" autocomplete="current-password" required
                 style="padding-right:2.8rem;"/>
          <button type="button" onclick="togglePw()" aria-label="Show/hide password"
            style="position:absolute;right:.7rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#444;font-size:1rem;">
            👁
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-pink btn-block btn-lg" id="login-submit-btn" style="margin-top:1.2rem;">
        ENTER THE DRIP ZONE →
      </button>
    </form>

    <div style="margin-top:1.3rem;text-align:center;">
      <a href="index.php" style="font-size:.78rem;color:#444;">← Back to Storefront</a>
    </div>

    <div style="margin-top:1rem;padding:.75rem;background:var(--dark3);border:1px solid #222;font-size:.72rem;color:#444;text-align:center;font-family:var(--font-body);">
      Default → <strong style="color:#666;">admin</strong> / <strong style="color:#666;">Admin@1234</strong>
      <br/><span style="color:#333;">Run reset_password.php if login fails</span>
    </div>
  </div>
</main>
<script>
function togglePw() {
  const pw = document.getElementById('password');
  pw.type = pw.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
