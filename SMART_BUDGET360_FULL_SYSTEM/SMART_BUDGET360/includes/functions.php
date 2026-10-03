<?php
function db(): PDO
{
    static $pdo = null;
    if (!$pdo) {
        $pdo = (new Database())->connect();
    }
    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid or expired CSRF token. Please refresh the page and try again.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function role_name(): string
{
    return $_SESSION['user']['role_name'] ?? '';
}

function home_path(): string
{
    return role_name() === 'Viewer' ? 'reports/index.php' : 'dashboard/index.php';
}

function is_admin(): bool
{
    return role_name() === 'Administrator';
}

function has_role(array|string $roles): bool
{
    $roles = (array)$roles;
    return in_array(role_name(), $roles, true);
}

function require_role(array|string $roles): void
{
    if (!has_role($roles)) {
        http_response_code(403);
        require ROOT_PATH . '/includes/access_denied.php';
        exit;
    }
}

function can_access(string $module): bool
{
    if (is_admin()) return true;
    $role = role_name();
    $map = [
        'dashboard' => ['Finance Manager', 'Sales Manager', 'Marketing Manager', 'Staff'],
        'budget' => ['Finance Manager'],
        'expenses' => ['Finance Manager', 'Staff'],
        'approvals' => ['Finance Manager', 'Sales Manager', 'Marketing Manager'],
        'sales' => ['Sales Manager'],
        'marketing' => ['Marketing Manager'],
        'reports' => ['Finance Manager', 'Sales Manager', 'Marketing Manager', 'Viewer'],
        'users' => [],
        'departments' => [],
        'audit' => [],
        'settings' => [],
    ];
    return in_array($role, $map[$module] ?? [], true);
}

function require_module(string $module): void
{
    if (!can_access($module)) {
        http_response_code(403);
        require ROOT_PATH . '/includes/access_denied.php';
        exit;
    }
}

function log_activity(string $action, string $module, string $description = ''): void
{
    try {
        $stmt = db()->prepare('INSERT INTO audit_logs (user_id, action, module, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            current_user()['id'] ?? null,
            $action,
            $module,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255),
        ]);
    } catch (Throwable $e) {
        // Logging must never block the main transaction.
    }
}

function money(float|int|string|null $amount): string
{
    return APP_CURRENCY_SYMBOL . number_format((float)$amount, 2);
}

function status_badge(string $status): string
{
    $class = match (strtolower($status)) {
        'approved', 'active', 'completed' => 'success',
        'rejected', 'inactive', 'cancelled' => 'danger',
        'pending', 'draft' => 'warning',
        default => 'secondary',
    };
    return '<span class="badge rounded-pill text-bg-' . $class . '">' . e(ucfirst($status)) . '</span>';
}

function generate_code(string $prefix, string $table, string $column): string
{
    $stamp = date('Ym');
    $like = $prefix . '-' . $stamp . '-%';
    $stmt = db()->prepare("SELECT {$column} FROM {$table} WHERE {$column} LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$like]);
    $last = $stmt->fetchColumn();
    $next = 1;
    if ($last && preg_match('/(\d{4})$/', $last, $m)) {
        $next = ((int)$m[1]) + 1;
    }
    return sprintf('%s-%s-%04d', $prefix, $stamp, $next);
}

function scalar(string $sql, array $params = []): float
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (float)($stmt->fetchColumn() ?: 0);
}

function departments(): array
{
    return db()->query('SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name')->fetchAll();
}

function categories(): array
{
    return db()->query('SELECT id, name FROM budget_categories WHERE is_active = 1 ORDER BY name')->fetchAll();
}

function users_for_select(): array
{
    return db()->query("SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS full_name, r.name role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.is_active=1 ORDER BY u.first_name, u.last_name")->fetchAll();
}

function request_department_allowed(int $departmentId): bool
{
    if (is_admin() || role_name() === 'Finance Manager') return true;
    return (int)(current_user()['department_id'] ?? 0) === $departmentId;
}

function setting(string $key, string $default = ''): string
{
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        $cache[$key] = $value !== false ? (string)$value : $default;
    } catch (Throwable $e) {
        $cache[$key] = $default;
    }
    return $cache[$key];
}

function save_approval_request(string $type, int $requestId): void
{
    $stmt = db()->prepare("INSERT INTO approvals (request_type, request_id, current_stage, status, submitted_by) VALUES (?, ?, 'department_manager', 'Pending', ?)");
    $stmt->execute([$type, $requestId, current_user()['id']]);
    $approvalId = (int)db()->lastInsertId();
    $hist = db()->prepare("INSERT INTO approval_history (approval_id, stage, action, action_by, remarks) VALUES (?, 'request', 'Submitted', ?, 'Request submitted for approval')");
    $hist->execute([$approvalId, current_user()['id']]);
}


function pending_approval_count(): int
{
    if (!can_access('approvals')) return 0;
    if (is_admin()) return (int)scalar("SELECT COUNT(*) FROM approvals WHERE status='Pending'");
    if (role_name() === 'Finance Manager') return (int)scalar("SELECT COUNT(*) FROM approvals WHERE status='Pending' AND current_stage='finance'");
    if (has_role(['Sales Manager','Marketing Manager'])) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM approvals a LEFT JOIN budgets b ON a.request_type='budget' AND b.id=a.request_id LEFT JOIN expenses e ON a.request_type='expense' AND e.id=a.request_id WHERE a.status='Pending' AND a.current_stage='department_manager' AND COALESCE(b.department_id,e.department_id)=?");
        $stmt->execute([(int)(current_user()['department_id'] ?? 0)]);
        return (int)$stmt->fetchColumn();
    }
    return 0;
}

function valid_date(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
