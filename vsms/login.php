<?php
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /vsms/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: /vsms/index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — VSMS</title>
<link rel="stylesheet" href="/vsms/assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <div style="font-size:42px">🔧</div>
      <h1 style="font-family:monospace;color:var(--accent);margin:10px 0 4px;font-size:22px;">VSMS</h1>
      <p style="color:var(--muted);font-size:12px;">Vehicle Service Center Management System</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control"
               placeholder="admin@vsms.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary"
              style="width:100%;justify-content:center;padding:12px;font-size:14px;margin-top:8px;">
        🔐 Sign In
      </button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:11px;color:var(--dim);">
      Default login: admin@vsms.com / admin123
    </p>
  </div>
</div>
</body>
</html>
