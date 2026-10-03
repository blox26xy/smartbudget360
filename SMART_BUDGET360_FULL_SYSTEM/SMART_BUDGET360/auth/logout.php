<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if (current_user()) log_activity('LOGOUT', 'Authentication', 'User signed out');
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
