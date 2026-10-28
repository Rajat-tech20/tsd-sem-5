<?php
// ============================================================
//  LOGIN PAGE — login.php
// ============================================================

session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);                // Prevent session fixation
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            redirect('index.php');
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — The Daily Chronicle</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .auth-page { min-height:100vh; display:flex; align-items:center; justify-content:center; background:var(--color-bg); }
    .auth-card { background:#fff; border:1px solid var(--color-border); border-radius:10px; padding:2.5rem 2rem; width:100%; max-width:420px; box-shadow:0 4px 20px rgba(0,0,0,0.07); }
    .auth-card h1 { font-family:var(--font-serif); font-size:1.625rem; margin-bottom:0.25rem; color:var(--color-ink); }
    .auth-card p { color:var(--color-ink-muted); font-size:0.9375rem; margin-bottom:1.75rem; }
    .auth-field { margin-bottom:1.25rem; }
    .auth-field label { display:block; font-weight:600; font-size:0.875rem; margin-bottom:0.375rem; color:var(--color-ink); }
    .auth-field input { width:100%; padding:0.75rem 1rem; border:1px solid var(--color-border); border-radius:4px; font-size:0.9375rem; font-family:var(--font-sans); outline:none; transition:border-color 0.2s; }
    .auth-field input:focus { border-color:var(--color-primary); }
    .auth-btn { width:100%; padding:0.8rem; background:var(--color-primary); color:#fff; border:none; border-radius:4px; font-size:1rem; font-weight:700; cursor:pointer; transition:background 0.2s; }
    .auth-btn:hover { background:var(--color-primary-dark); }
    .auth-error { background:#fff0f2; border:1px solid #f5c0c7; color:#c41e3a; padding:0.75rem 1rem; border-radius:4px; font-size:0.875rem; margin-bottom:1.25rem; }
    .auth-footer { text-align:center; margin-top:1.25rem; font-size:0.875rem; color:var(--color-ink-muted); }
    .auth-footer a { color:var(--color-primary); font-weight:600; }
    .auth-logo { display:flex; align-items:center; gap:0.5rem; margin-bottom:1.75rem; }
  </style>
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <span class="logo__mark">DC</span>
      <span style="font-family:var(--font-serif); font-weight:700; font-size:1.125rem;">The Daily Chronicle</span>
    </div>
    <h1>Sign In</h1>
    <p>Welcome back. Sign in to your account.</p>

    <?php if ($error): ?>
    <div class="auth-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="auth-field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
      </div>
      <div class="auth-field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="auth-btn">Sign In</button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="register.php">Register here</a>
    </div>
  </div>
</div>
</body>
</html>
