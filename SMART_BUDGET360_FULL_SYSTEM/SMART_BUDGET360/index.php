<?php
require_once __DIR__ . '/includes/bootstrap.php';
redirect(current_user() ? home_path() : 'auth/login.php');
