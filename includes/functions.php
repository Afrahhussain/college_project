<?php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

// Flash helpers
function flash_set(string $key, string $msg): void { $_SESSION['flash_'.$key] = $msg; }
function flash_get(string $key): ?string {
    $k = 'flash_'.$key;
    if (!empty($_SESSION[$k])) { $m = $_SESSION[$k]; unset($_SESSION[$k]); return $m; }
    return null;
}

// Auth helpers
function is_logged_in(): bool { return !empty($_SESSION['user_id']); }
function current_user(): ?array {
    return is_logged_in() ? [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['name'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'email' => $_SESSION['email'] ?? ''
    ] : null;
}
function require_login(): void {
    if (!is_logged_in()) { header('Location: /college_project/login.php'); exit; }
}
function require_role(string $role): void {
    require_login();
    $r = $_SESSION['role'] ?? '';
    // map 'incharge' vs 'class_incharge' if needed
    if ($r !== $role && $r !== 'admin') {
        http_response_code(403);
        echo 'Forbidden: insufficient permissions.';
        exit;
    }
}

// CSRF
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf($token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

// Mail placeholder
function send_mail($to, $subject, $body) {
    // replace with PHPMailer in production
    return mail($to, $subject, $body, "Content-type: text/html; charset=utf-8\r\n");
}
