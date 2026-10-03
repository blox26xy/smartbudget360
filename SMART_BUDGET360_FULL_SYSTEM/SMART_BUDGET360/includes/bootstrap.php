<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_name('SB360SESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

require_once dirname(__DIR__) . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

if (!empty($_SESSION['user'])) {
    $idleLimit = 30 * 60;
    if (!empty($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > $idleLimit) {
        log_activity('SESSION_TIMEOUT', 'Authentication', 'Session expired after inactivity');
        $_SESSION = [];
        session_regenerate_id(true);
        if (!str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/auth/')) {
            redirect('auth/login.php?expired=1');
        }
    } else {
        $_SESSION['last_activity'] = time();
    }
}

if (empty($_SESSION['user']) && !str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/auth/')) {
    redirect('auth/login.php');
}
