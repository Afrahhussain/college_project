<?php
// admin/upload_marks.php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../includes/header.php';

$msg=''; $error=''; $report=null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf_token'] ?? '';
  if (!verify_csrf($token)) $error='Invalid CSRF token.';
  elseif (!isset($_FILES['csv_file'])) $error='No file uploaded.';
  else {
    $f = $_FILES['csv_file'];
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] <= 0) $error='Upload error.';
    else {
      $tmp = $f['tmp_name']; $inserted=0; $skipped=0; $errors=[];
      $pdo->beginTransaction();
      try {
        if (($h = fopen($tmp,'r')) !== false) {
          $row=0;
          while (($data = fgetcsv($h,2000,',')) !== false) {
            $row++;
            if ($row===1) {
              $hdr = array_map('strtolower',$data);
              if (in_array('student_id',$hdr) || in_array('email',$hdr)) { continue; }
            }
            $identifier = trim($data[0] ?? '');
            $subject = trim($data[1] ?? '');
            $marks_obtained = trim($data[2] ?? '');
            $max_marks = trim($data[3] ?? '');
            $exam_type = trim($data[4] ?? '');

            if (!$identifier || !$subject) { $skipped++; $errors[]="Row {$row}: missing"; continue; }

            if (ctype_digit($identifier)) {
              $student_id = (int)$identifier;
              $chk = $pdo->prepare("SELECT id FROM users WHERE id=? AND role='student' LIMIT 1"); $chk->execute([$student_id]);
              if (!$chk->fetch()) { $skipped++; $errors[]="Row {$row}: student id not found"; continue; }
            } else {
              $chk = $pdo->prepare("SELECT id FROM users WHERE email=? AND role='student' LIMIT 1"); $chk->execute([$identifier]); $r = $chk->fetch();
              if (!$r) { $skipped++; $errors[]="Row {$row}: email not found"; continue; }
              $student_id = (int)$r['id'];
            }

            if (!is_numeric($marks_obtained) || !is_numeric($max_marks)) { $skipped++; $errors[]="Row {$row}: marks numeric"; continue; }
            $marks_obtained = floatval($marks_obtained); $max_marks = floatval($max_marks);
            if ($marks_obtained < 0 || $max_marks <= 0 || $marks_obtained > $max_marks) { $skipped++; $errors[]="Row {$row}: invalid marks values"; continue; }

            $stmt = $pdo->prepare("INSERT INTO marks (student_id,subject,marks_obtained,max_marks,exam_type,created_at) VALUES (?,?,?,?,?,NOW())");
            $stmt->execute([$student_id,$subject,$marks_obtained,$max_marks,$exam_type]);
            $inserted++;
          }
          fclose($h);
        } else { $pdo->rollBack(); $error='Could not open file.'; }
        $pdo->commit();
        $msg = "Inserted: {$inserted}, Skipped: {$skipped}";
        $report = ['inserted'=>$inserted,'skipped'=>$skipped,'errors'=>$errors];
      } catch (Exception $e) {
        $pdo->rollBack(); $error='DB error: '.$e->getMessage();
      }
    }
  }
}
$token = csrf_token();
?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Upload Marks</h3>
    <a href="/college_project/admin/dashboard.php" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?=htmlspecialchars($msg)?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>

  <div class="card p-3">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($token)?>">
      <div class="mb-3">
        <label class="form-label">CSV (student_id or email,subject,marks_obtained,max_marks,exam_type)</label>
        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
      </div>
      <button class="btn btn-primary">Upload Marks</button>
    </form>
  </div>

  <?php if ($report): ?>
    <div class="card p-3 mt-3">
      <h6>Report</h6>
      <p>Inserted: <?= (int)$report['inserted'] ?> — Skipped: <?= (int)$report['skipped'] ?></p>
      <?php if (!empty($report['errors'])): ?><div class="alert alert-warning"><ul><?php foreach($report['errors'] as $er): ?><li><?=htmlspecialchars($er)?></li><?php endforeach;?></ul></div><?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
