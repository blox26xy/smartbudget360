<?php
$current = $activeMenu ?? '';
function nav_active(string $key): string { global $current; return $current === $key ? 'active' : ''; }
function nav_open(array $keys): string { global $current; return in_array($current, $keys, true) ? 'show' : ''; }
?>
<aside class="sidebar" id="sidebar">
  <div class="brand-wrap">
    <div class="brand-mark"><i class="fa-solid fa-chart-pie"></i></div>
    <div class="brand-text"><strong>SMART</strong><span>BUDGET360</span><small>Sales & Marketing ERP</small></div>
  </div>
  <div class="sidebar-scroll">
    <div class="menu-label">MAIN MENU</div>
    <?php if (can_access('dashboard')): ?><a class="nav-item <?= nav_active('dashboard') ?>" href="<?= e(url('dashboard/index.php')) ?>"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a><?php endif; ?>

    <?php if (can_access('budget')): ?>
    <button class="nav-item nav-toggle <?= nav_open(['budget-list','budget-add','budget-categories','budget-reports']) ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#budgetMenu"><i class="fa-solid fa-wallet"></i><span>Budget Management</span><i class="fa-solid fa-chevron-down ms-auto chevron"></i></button>
    <div class="collapse submenu <?= nav_open(['budget-list','budget-add','budget-categories','budget-reports']) ?>" id="budgetMenu">
      <a class="<?= nav_active('budget-list') ?>" href="<?= e(url('budget/index.php')) ?>">Budget List</a>
      <a class="<?= nav_active('budget-add') ?>" href="<?= e(url('budget/create.php')) ?>">Add Budget</a>
      <a class="<?= nav_active('budget-categories') ?>" href="<?= e(url('budget/categories.php')) ?>">Budget Categories</a>
      <a class="<?= nav_active('budget-reports') ?>" href="<?= e(url('budget/reports.php')) ?>">Budget Reports</a>
    </div>
    <?php endif; ?>

    <?php if (can_access('expenses')): ?>
    <button class="nav-item nav-toggle <?= nav_open(['expense-list','expense-new','expense-upload','expense-reports']) ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#expenseMenu"><i class="fa-solid fa-receipt"></i><span>Expense Monitoring</span><i class="fa-solid fa-chevron-down ms-auto chevron"></i></button>
    <div class="collapse submenu <?= nav_open(['expense-list','expense-new','expense-upload','expense-reports']) ?>" id="expenseMenu">
      <a class="<?= nav_active('expense-list') ?>" href="<?= e(url('expenses/index.php')) ?>">Expense List</a>
      <a class="<?= nav_active('expense-new') ?>" href="<?= e(url('expenses/create.php')) ?>">New Expense Request</a>
      <a class="<?= nav_active('expense-upload') ?>" href="<?= e(url('expenses/upload.php')) ?>">Receipt Upload</a>
      <a class="<?= nav_active('expense-reports') ?>" href="<?= e(url('expenses/reports.php')) ?>">Expense Reports</a>
    </div>
    <?php endif; ?>

    <?php if (can_access('approvals')): $pendingApprovalCount = pending_approval_count(); ?><a class="nav-item <?= nav_active('approvals') ?>" href="<?= e(url('approvals/index.php')) ?>"><i class="fa-solid fa-circle-check"></i><span>Approvals</span><?php if ($pendingApprovalCount > 0): ?><span class="badge bg-warning-subtle text-warning ms-auto" id="approvalCount"><?= $pendingApprovalCount ?></span><?php endif; ?></a><?php endif; ?>

    <?php if (can_access('sales')): ?>
    <button class="nav-item nav-toggle <?= nav_open(['sales-dashboard','sales-entry','sales-target','sales-reports']) ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#salesMenu"><i class="fa-solid fa-arrow-trend-up"></i><span>Sales Monitoring</span><i class="fa-solid fa-chevron-down ms-auto chevron"></i></button>
    <div class="collapse submenu <?= nav_open(['sales-dashboard','sales-entry','sales-target','sales-reports']) ?>" id="salesMenu">
      <a class="<?= nav_active('sales-dashboard') ?>" href="<?= e(url('sales/index.php')) ?>">Sales Dashboard</a>
      <a class="<?= nav_active('sales-entry') ?>" href="<?= e(url('sales/entry.php')) ?>">Sales Entry</a>
      <a class="<?= nav_active('sales-target') ?>" href="<?= e(url('sales/targets.php')) ?>">Sales Target</a>
      <a class="<?= nav_active('sales-reports') ?>" href="<?= e(url('sales/reports.php')) ?>">Sales Reports</a>
    </div>
    <?php endif; ?>

    <?php if (can_access('marketing')): ?>
    <button class="nav-item nav-toggle <?= nav_open(['campaign-list','campaign-add','campaign-expenses','campaign-roi']) ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#marketingMenu"><i class="fa-solid fa-bullhorn"></i><span>Marketing Campaigns</span><i class="fa-solid fa-chevron-down ms-auto chevron"></i></button>
    <div class="collapse submenu <?= nav_open(['campaign-list','campaign-add','campaign-expenses','campaign-roi']) ?>" id="marketingMenu">
      <a class="<?= nav_active('campaign-list') ?>" href="<?= e(url('marketing/index.php')) ?>">Campaign List</a>
      <a class="<?= nav_active('campaign-add') ?>" href="<?= e(url('marketing/create.php')) ?>">Add Campaign</a>
      <a class="<?= nav_active('campaign-expenses') ?>" href="<?= e(url('marketing/expenses.php')) ?>">Campaign Expenses</a>
      <a class="<?= nav_active('campaign-roi') ?>" href="<?= e(url('marketing/roi.php')) ?>">ROI Analysis</a>
    </div>
    <?php endif; ?>

    <?php if (can_access('reports')): ?><a class="nav-item <?= nav_active('reports') ?>" href="<?= e(url('reports/index.php')) ?>"><i class="fa-solid fa-file-lines"></i><span>Reports</span></a><?php endif; ?>

    <?php if (is_admin()): ?>
    <div class="menu-label mt-4">ADMINISTRATION</div>
    <a class="nav-item <?= nav_active('users') ?>" href="<?= e(url('users/index.php')) ?>"><i class="fa-solid fa-users-gear"></i><span>Users & Roles</span></a>
    <a class="nav-item <?= nav_active('departments') ?>" href="<?= e(url('departments/index.php')) ?>"><i class="fa-solid fa-building"></i><span>Departments</span></a>
    <a class="nav-item <?= nav_active('audit') ?>" href="<?= e(url('audit/index.php')) ?>"><i class="fa-solid fa-clock-rotate-left"></i><span>Audit Logs</span></a>
    <a class="nav-item <?= nav_active('settings') ?>" href="<?= e(url('settings/index.php')) ?>"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
    <?php endif; ?>
  </div>
</aside>
