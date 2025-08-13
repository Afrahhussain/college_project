<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../includes/header.php';

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();
?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Admin Dashboard</h3>
    <a href="/college_project/admin/manage_users.php" class="btn btn-outline-primary">Manage Users</a>
  </div>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card p-3">
        <small class="text-muted">Total Users</small>
        <h2 class="mb-0"><?= (int)$totalUsers ?></h2>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <small class="text-muted">Students</small>
        <h2 class="mb-0"><?= (int)$totalStudents ?></h2>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <small class="text-muted">Pending Approvals</small>
        <h2 class="mb-0"><?= (int)$pending ?></h2>
      </div>
    </div>
  </div>

  <div class="card mt-4 p-3">
    <h5>Recent Registrations</h5>
    <div class="table-responsive mt-2">
      <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($pdo->query("SELECT name,email,role,status FROM users ORDER BY id DESC LIMIT 8")->fetchAll() as $r): ?>
            <tr>
              <td><?=htmlspecialchars($r['name'])?></td>
              <td><?=htmlspecialchars($r['email'])?></td>
              <td><?=htmlspecialchars($r['role'])?></td>
              <td><span class="badge bg-<?= $r['status']==='approved' ? 'success' : 'warning' ?>"><?=htmlspecialchars($r['status'])?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
