<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../includes/header.php';

$msg=''; $error='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $t = $_POST['csrf_token'] ?? '';
  if (!verify_csrf($t)) { $error='Invalid CSRF token.'; }
  else {
    if (isset($_POST['action']) && $_POST['action']==='delete' && isset($_POST['id'])) {
      $id = (int)$_POST['id'];
      $pdo->prepare("DELETE FROM class_allotments WHERE id=?")->execute([$id]);
      $msg='Allotment removed.';
    } else {
      $faculty_id = (int)($_POST['faculty_id'] ?? 0);
      $branch = trim($_POST['branch'] ?? '');
      $year = trim($_POST['year'] ?? '');
      $section = trim($_POST['section'] ?? '');
      if (!$faculty_id || !$branch || !$year || !$section) { $error='All fields required.'; }
      else {
        $ch = $pdo->prepare("SELECT id FROM class_allotments WHERE branch=? AND year=? AND section=? LIMIT 1");
        $ch->execute([$branch,$year,$section]);
        if ($ch->fetch()) { $error='Class already assigned.'; }
        else {
          $pdo->prepare("INSERT INTO class_allotments (faculty_id,branch,year,section,is_incharge,created_at) VALUES (?,?,?,?,0,NOW())")
              ->execute([$faculty_id,$branch,$year,$section]);
          $msg='Class allotted.';
        }
      }
    }
  }
}

$faculty = $pdo->query("SELECT id,name,branch FROM users WHERE role IN ('faculty','incharge') ORDER BY name ASC")->fetchAll();
$allotments = $pdo->query("SELECT ca.id,u.name AS faculty_name,ca.branch,ca.year,ca.section FROM class_allotments ca LEFT JOIN users u ON ca.faculty_id=u.id ORDER BY ca.branch,ca.year,ca.section")->fetchAll();
$token = csrf_token();
?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Class Allotment</h3>
    <a href="/college_project/admin/dashboard.php" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?=htmlspecialchars($msg)?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>

  <div class="card p-3 mb-3">
    <form method="post" class="row g-3 align-items-end">
      <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($token)?>">
      <div class="col-md-4">
        <label class="form-label">Faculty</label>
        <select name="faculty_id" class="form-select" required>
          <option value="">Select faculty</option>
          <?php foreach($faculty as $f): ?>
            <option value="<?= (int)$f['id'] ?>"><?=htmlspecialchars($f['name'].' ('.$f['branch'].')')?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Branch</label>
        <select name="branch" class="form-select" required>
          <option value="">Branch</option><option>CSE</option><option>ECE</option><option>EEE</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Year</label>
        <select name="year" class="form-select" required><option value="">Year</option><option>1</option><option>2</option><option>3</option><option>4</option></select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Section</label>
        <select name="section" class="form-select" required><option value="">Section</option><option>A</option><option>B</option><option>C</option><option>D</option></select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100">Assign</button>
      </div>
    </form>
  </div>

  <div class="card p-3">
    <h6>Existing Allotments</h6>
    <div class="table-responsive mt-2">
      <table class="table">
        <thead><tr><th>Faculty</th><th>Branch</th><th>Year</th><th>Section</th><th>Action</th></tr></thead>
        <tbody>
          <?php if (empty($allotments)): ?>
            <tr><td colspan="5" class="text-muted text-center">No allotments</td></tr>
          <?php else: foreach($allotments as $a): ?>
            <tr>
              <td><?=htmlspecialchars($a['faculty_name']?:'—')?></td>
              <td><?=htmlspecialchars($a['branch'])?></td>
              <td><?=htmlspecialchars($a['year'])?></td>
              <td><?=htmlspecialchars($a['section'])?></td>
              <td>
                <form method="post" class="d-inline" onsubmit="return confirm('Remove?');">
                  <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($token)?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                  <button class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
