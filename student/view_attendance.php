<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('student');
require_once __DIR__ . '/../includes/header.php';
$student_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT subject,classes_attended,total_classes,month,created_at FROM attendance WHERE student_id=? ORDER BY created_at DESC");
$stmt->execute([$student_id]);
$attendance = $stmt->fetchAll();
?>
<div class="container">
  <h3>My Attendance</h3>
  <div class="card mt-3 p-3">
    <?php if ($attendance): ?>
      <div class="table-responsive"><table class="table"><thead><tr><th>Subject</th><th>Attended</th><th>Total</th><th>Month</th><th>Date</th></tr></thead><tbody>
        <?php foreach ($attendance as $a): ?>
          <tr>
            <td><?=htmlspecialchars($a['subject'])?></td>
            <td><?= (int)$a['classes_attended'] ?></td>
            <td><?= (int)$a['total_classes'] ?></td>
            <td><?=htmlspecialchars($a['month'])?></td>
            <td><?=htmlspecialchars($a['created_at'])?></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    <?php else: ?>
      <div class="alert alert-info">No attendance records yet.</div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
