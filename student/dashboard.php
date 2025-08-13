<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('student');
require_once __DIR__ . '/../includes/header.php';

$student_id = $_SESSION['user_id'];
$countMarks = $pdo->prepare("SELECT COUNT(*) FROM marks WHERE student_id=?"); $countMarks->execute([$student_id]); $nMarks=$countMarks->fetchColumn();
$countAtt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id=?"); $countAtt->execute([$student_id]); $nAtt = $countAtt->fetchColumn();
?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center">
    <h3>Student Dashboard</h3>
  </div>

  <div class="row g-3 mt-3">
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Marks entries</small><h3><?= (int)$nMarks ?></h3></div></div>
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Attendance records</small><h3><?= (int)$nAtt ?></h3></div></div>
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Quick Links</small><div class="mt-2"><a class="btn btn-outline-primary btn-sm me-2" href="/college_project/student/view_marks.php">View Marks</a><a class="btn btn-outline-primary btn-sm" href="/college_project/student/view_attendance.php">View Attendance</a></div></div></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
