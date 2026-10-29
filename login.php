<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/models/User.php';
if (!empty($_SESSION['user'])) { header('Location: /fund_tracker/index.php'); exit; }
header('Cache-Control: no-store');
$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'since' => time()];
    if (time() - $attempts['since'] >= 300) $attempts = ['count' => 0, 'since' => time()];
    if ($attempts['count'] >= 10) {
        $error = 'Too many attempts. Please try again in five minutes.';
        http_response_code(429);
    } else {
        $user = (new User((new Database())->getConnection()))->authenticate($username, $password);
        if ($user) {
            session_regenerate_id(true);
            $_SESSION = ['user' => $user, 'csrf_token' => bin2hex(random_bytes(32))];
            header('Location: /fund_tracker/index.php'); exit;
        }
        $attempts['count']++;
        $_SESSION['login_attempts'] = $attempts;
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sign in | Fund Tracker</title><link rel="stylesheet" href="/fund_tracker/assets/css/bootstrap.min.css"><link rel="stylesheet" href="/fund_tracker/assets/css/app.css"></head>
<body class="login-page"><main class="login-card">
<a class="navbar-brand mb-4" href="login.php"><span class="brand-mark" aria-hidden="true">F</span> Fund Tracker</a>
<p class="eyebrow">WELCOME BACK</p><h1>Sign in to your funds</h1><p class="text-muted mb-4">Keep your deposits, expenses, and balance in view.</p>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" action="login.php">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
<div class="mb-3"><label for="username" class="form-label">Username</label><input class="form-control" id="username" name="username" autocomplete="username" maxlength="64" required autofocus value="<?= htmlspecialchars($username) ?>"></div>
<div class="mb-4"><label for="password" class="form-label">Password</label><input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required></div>
<button class="btn btn-primary w-100" type="submit">Sign in</button>
</form><p class="text-muted small mt-4 mb-0">Your shared workspace for better money habits.</p>
</main></body></html>
