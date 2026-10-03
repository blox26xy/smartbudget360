<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$activeMenu = $activeMenu ?? '';
$bodyClass = $bodyClass ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <link href="<?= e(url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?>">
<div class="app-shell">
<?php require ROOT_PATH . '/includes/sidebar.php'; ?>
<div class="app-main">
<?php require ROOT_PATH . '/includes/topbar.php'; ?>
<main class="content-wrap">
<?php if ($flash = pull_flash()): ?>
<div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm" role="alert"><?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
