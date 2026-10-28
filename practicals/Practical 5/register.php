<?php
// ============================================================
//  REGISTER PAGE — register.php
// ============================================================

session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) redirect('index.php');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check for duplicate email
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'reader')");
            $ins->execute([$name, $email, $hash]);
            $success = 'Account created! You can now <a href="login.php">sign in</a>.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — The Daily Chronicle</title>
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
    .auth-success { background:#f0fff4; border:1px solid #b2dfdb; color:#1b5e20; padding:0.75rem 1rem; border-radius:4px; font-size:0.875rem; margin-bottom:1.25rem; }
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
    <h1>Create Account</h1>
    <p>Join our community of readers.</p>

    <?php if ($error): ?>
    <div class="auth-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="auth-success"><?= $success /* contains safe HTML link */ ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" action="register.php">
      <div class="auth-field">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Your full name"
               value="<?= e($_POST['name'] ?? '') ?>" required autofocus>
      </div>
      <div class="auth-field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               value="<?= e($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="auth-field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
      </div>
      <div class="auth-field">
        <label for="confirm">Confirm Password</label>
        <input type="password" id="confirm" name="confirm" placeholder="Repeat password" required>
      </div>
      <button type="submit" class="auth-btn">Create Account</button>
    </form>
    <?php endif; ?>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
  </div>
</div>
</body>
</html>
