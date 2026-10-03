<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if (current_user()) redirect(home_path());
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $stmt = db()->prepare('SELECT u.*, r.name role_name, d.name department_name FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN departments d ON d.id=u.department_id WHERE u.username=? AND u.is_active=1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            $_SESSION['last_activity'] = time();
            log_activity('LOGIN', 'Authentication', 'User signed in');
            redirect(home_path());
        }
        $error = 'Invalid username or password.';
        log_activity('FAILED_LOGIN', 'Authentication', 'Failed login attempt for username: ' . $username);
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login | SMART BUDGET360</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet"><link href="<?= e(url('assets/css/app.css')) ?>" rel="stylesheet"></head>
<body class="login-page"><div class="login-card"><section class="login-hero"><div class="login-brand"><div class="brand-mark"><i class="fa-solid fa-chart-pie"></i></div><div><strong class="fs-5">SMART BUDGET360</strong><div class="small text-white-50">Enterprise Budget Intelligence</div></div></div><h1>Control every peso.<br>Measure every result.</h1><p>A centralized financial operating system for Sales and Marketing budget allocation, expenses, approvals, campaign ROI, and performance reporting.</p><div class="login-points"><div><i class="fa-solid fa-circle-check"></i> Role-based enterprise access</div><div><i class="fa-solid fa-circle-check"></i> Real-time budget utilization</div><div><i class="fa-solid fa-circle-check"></i> Approval workflow & audit trail</div></div></section><section class="login-form"><div class="mb-5"><div class="text-primary fw-bold small mb-2">SECURE ACCESS</div><h2>Welcome back</h2><p class="muted">Sign in to your SMART BUDGET360 workspace.</p></div><?php if(isset($_GET['expired'])): ?><div class="alert alert-warning">Your session expired after 30 minutes of inactivity. Please sign in again.</div><?php endif; ?><?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" autocomplete="off"><?= csrf_field() ?><div class="mb-3"><label class="form-label">Username</label><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="fa-regular fa-user text-secondary"></i></span><input class="form-control border-start-0" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus></div></div><div class="mb-4"><label class="form-label">Password</label><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-lock text-secondary"></i></span><input class="form-control border-start-0" type="password" name="password" required></div></div><button class="btn btn-primary w-100 py-2 fw-semibold">Sign In <i class="fa-solid fa-arrow-right ms-2"></i></button></form><div class="mt-4 p-3 rounded-3 bg-light small text-secondary"><strong>Default Administrator</strong><br>Username: <code>admin</code> &nbsp; Password: <code>admin123</code></div></section></div></body></html>
