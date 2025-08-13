<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) { flash_set('error','Invalid token'); header('Location: manage_users.php'); exit; }

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$action = $_POST['action'] ?? '';

if (!$user_id || !$action) { flash_set('error','Missing params'); header('Location: manage_users.php'); exit; }

if ($action === 'approve') {
    $pdo->prepare("UPDATE users SET status='approved',approved_at=NOW() WHERE id=?")->execute([$user_id]);
    flash_set('msg','User approved.');
} elseif ($action === 'reject') {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$user_id]);
    flash_set('msg','User rejected and deleted.');
} elseif ($action === 'revoke') {
    $pdo->prepare("UPDATE users SET status='pending' WHERE id=?")->execute([$user_id]);
    flash_set('msg','User revoked to pending.');
} else {
    flash_set('error','Unknown action.');
}
header('Location: manage_users.php');
exit;
