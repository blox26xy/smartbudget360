<?php $topPending = can_access('approvals') ? pending_approval_count() : 0; ?>
<header class="topbar">
  <button class="btn sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><i class="fa-solid fa-bars"></i></button>
  <div class="search-wrap d-none d-md-flex"><i class="fa-solid fa-magnifying-glass"></i><input id="globalSearch" type="search" placeholder="Search this page..." aria-label="Search"></div>
  <div class="topbar-spacer"></div>
  <div class="date-pill d-none d-lg-flex"><i class="fa-regular fa-calendar"></i><span><?= e(date('D, M d, Y')) ?></span></div>
  <div class="dropdown">
    <button class="btn icon-btn position-relative" data-bs-toggle="dropdown"><i class="fa-regular fa-bell"></i><?php if($topPending>0): ?><span class="notif-dot"></span><?php endif; ?></button>
    <div class="dropdown-menu dropdown-menu-end p-0 notification-menu">
      <div class="p-3 border-bottom"><strong>Notifications</strong></div>
      <div class="p-3 text-secondary small"><?php if($topPending>0): ?><strong class="text-dark"><?= $topPending ?></strong> approval request(s) are waiting in your queue.<?php elseif(can_access('approvals')): ?>No approval requests are waiting in your queue.<?php else: ?>No actionable notifications at this time.<?php endif; ?></div>
    </div>
  </div>
  <a class="btn icon-btn d-none d-md-grid" style="place-items:center" href="<?= e(url('auth/logout.php')) ?>" title="Logout" aria-label="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
  <div class="dropdown">
    <button class="btn profile-btn" data-bs-toggle="dropdown"><span class="avatar"><?= e(strtoupper(substr(current_user()['first_name'] ?? 'U',0,1) . substr(current_user()['last_name'] ?? '',0,1))) ?></span><span class="d-none d-md-block text-start"><strong><?= e((current_user()['first_name'] ?? '') . ' ' . (current_user()['last_name'] ?? '')) ?></strong><small><?= e(role_name()) ?></small></span><i class="fa-solid fa-chevron-down small"></i></button>
    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
      <li><span class="dropdown-item-text small text-secondary">@<?= e(current_user()['username'] ?? '') ?></span></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="<?= e(url('auth/logout.php')) ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
    </ul>
  </div>
</header>
