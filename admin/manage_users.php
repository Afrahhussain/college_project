<?php
// admin/manage_users.php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../includes/header.php';

$BASE_URL = '/college_project'; // same base used in header; update if needed

// Filters
$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$branch_filter = $_GET['branch'] ?? '';

// pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where = "WHERE 1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($role_filter !== '') {
    $where .= " AND role = ?";
    $params[] = $role_filter;
}
if ($status_filter !== '') {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}
if ($branch_filter !== '') {
    $where .= " AND branch = ?";
    $params[] = $branch_filter;
}

// total count for pagination
$countSql = "SELECT COUNT(*) FROM users $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));

// fetch page
$sql = "SELECT id, name, email, role, branch, year, section, status, created_at FROM users $where ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// helper for building query string for pagination links
function qs($overrides = []) {
    $q = array_merge($_GET, $overrides);
    return http_build_query($q);
}
?>

<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-md-8">
      <h3 class="mb-0">Manage Users</h3>
      <small class="text-muted">Approve, revoke or reject registrations</small>
    </div>
    <div class="col-md-4 text-md-end">
      <a class="btn btn-sm btn-primary" href="<?= $BASE_URL ?>/admin/upload_students_csv.php"><i class="bi bi-upload"></i> Bulk Upload</a>
    </div>
  </div>

  <div class="card mb-3 table-card">
    <div class="card-body">
      <form class="row g-2 align-items-center mb-3" method="get">
        <div class="col-auto">
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or email" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-auto">
          <select name="role" class="form-select form-select-sm">
            <option value="">All Roles</option>
            <option value="admin" <?= $role_filter==='admin'?'selected':'' ?>>Admin</option>
            <option value="faculty" <?= $role_filter==='faculty'?'selected':'' ?>>Faculty</option>
            <option value="hod" <?= $role_filter==='hod'?'selected':'' ?>>HOD</option>
            <option value="incharge" <?= $role_filter==='incharge'?'selected':'' ?>>Class Incharge</option>
            <option value="student" <?= $role_filter==='student'?'selected':'' ?>>Student</option>
          </select>
        </div>
        <div class="col-auto">
          <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="approved" <?= $status_filter==='approved'?'selected':'' ?>>Approved</option>
            <option value="pending" <?= $status_filter==='pending'?'selected':'' ?>>Pending</option>
            <option value="rejected" <?= $status_filter==='rejected'?'selected':'' ?>>Rejected</option>
            <option value="revoked" <?= $status_filter==='revoked'?'selected':'' ?>>Revoked</option>
          </select>
        </div>
        <div class="col-auto">
          <select name="branch" class="form-select form-select-sm">
            <option value="">All Branches</option>
            <option value="CSE" <?= $branch_filter==='CSE'?'selected':'' ?>>CSE</option>
            <option value="ECE" <?= $branch_filter==='ECE'?'selected':'' ?>>ECE</option>
            <option value="EEE" <?= $branch_filter==='EEE'?'selected':'' ?>>EEE</option>
          </select>
        </div>
        <div class="col-auto">
          <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        </div>
        <div class="col text-end">
          <span class="text-muted">Total: <?= $total ?> users</span>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Branch</th>
              <th>Year</th>
              <th>Section</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$users): ?>
              <tr><td colspan="9" class="text-center py-4 text-muted">No users found.</td></tr>
            <?php else: foreach ($users as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['id']) ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                <td><?= htmlspecialchars($u['branch']) ?></td>
                <td><?= htmlspecialchars($u['year']) ?></td>
                <td><?= htmlspecialchars($u['section']) ?></td>
                <td>
                  <?php if ($u['status'] === 'approved'): ?>
                    <span class="badge bg-success">Approved</span>
                  <?php elseif ($u['status'] === 'pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                  <?php elseif ($u['status'] === 'rejected'): ?>
                    <span class="badge bg-danger">Rejected</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($u['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <?php if ($u['status'] === 'pending'): ?>
                    <a href="<?= $BASE_URL ?>/admin/manage_users_action.php?action=approve&id=<?= $u['id'] ?>&csrf=<?= csrf_token() ?>"
                       class="btn btn-sm btn-success confirm-action" data-confirm="Approve this user?">Approve</a>
                    <a href="<?= $BASE_URL ?>/admin/manage_users_action.php?action=reject&id=<?= $u['id'] ?>&csrf=<?= csrf_token() ?>"
                       class="btn btn-sm btn-outline-danger confirm-action ms-1" data-confirm="Reject and delete this user?">Reject</a>
                  <?php elseif ($u['status'] === 'approved'): ?>
                    <a href="<?= $BASE_URL ?>/admin/manage_users_action.php?action=revoke&id=<?= $u['id'] ?>&csrf=<?= csrf_token() ?>"
                       class="btn btn-sm btn-warning confirm-action" data-confirm="Revoke this user's approval?">Revoke</a>
                  <?php else: ?>
                    <a href="<?= $BASE_URL ?>/admin/manage_users_action.php?action=approve&id=<?= $u['id'] ?>&csrf=<?= csrf_token() ?>"
                       class="btn btn-sm btn-success confirm-action" data-confirm="Approve this user?">Approve</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mt-3">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= qs(['page'=> $page-1]) ?>">Previous</a>
          </li>
          <?php
            $start = max(1, $page - 3);
            $end = min($total_pages, $page + 3);
            for ($p = $start; $p <= $end; $p++): ?>
          <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= qs(['page'=>$p]) ?>"><?= $p ?></a>
          </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= qs(['page'=> $page+1]) ?>">Next</a>
          </li>
        </ul>
      </nav>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
