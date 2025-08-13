<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('student');
require_once __DIR__ . '/../includes/header.php';
$student_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT subject,marks_obtained,max_marks,exam_type,created_at FROM marks WHERE student_id=? ORDER BY created_at DESC");
$stmt->execute([$student_id]);
$marks = $stmt->fetchAll();
?>
<div class="container">
  <h3>My Marks</h3>
  <div class="card mt-3 p-3">
    <?php if ($marks): ?>
      <div class="table-responsive"><table class="table"><thead><tr><th>Subject</th><th>Marks</th><th>Max</th><th>Exam</th><th>Date</th></tr></thead><tbody>
        <?php foreach ($marks as $m): ?>
          <tr>
            <td><?=htmlspecialchars($m['subject'])?></td>
            <td><?=htmlspecialchars($m['marks_obtained'])?></td>
            <td><?=htmlspecialchars($m['max_marks'])?></td>
            <td><?=htmlspecialchars($m['exam_type'])?></td>
            <td><?=htmlspecialchars($m['created_at'])?></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    <?php else: ?>
      <div class="alert alert-info">No marks found.</div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
