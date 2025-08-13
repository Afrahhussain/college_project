<?php
// admin/upload_faculty_csv.php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../includes/header.php';

$msg=''; $error=''; $report=null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) { $error='Invalid CSRF token.'; }
    elseif (!isset($_FILES['csv_file'])) { $error='No file uploaded.'; }
    else {
        $f = $_FILES['csv_file'];
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] <= 0) { $error='Upload error or empty file.'; }
        else {
            $tmp = $f['tmp_name'];
            $inserted = $skipped = 0; $errors = [];
            $pdo->beginTransaction();
            try {
                if (($h = fopen($tmp,'r')) !== false) {
                    $row=0;
                    while (($data = fgetcsv($h,2000,',')) !== false) {
                        $row++;
                        if ($row===1) {
                          $head = array_map('strtolower',$data);
                          if (in_array('email',$head) || in_array('name',$head)) { continue; }
                        }
                        $name = trim($data[0] ?? '');
                        $email = strtolower(trim($data[1] ?? ''));
                        $passwordRaw = trim($data[2] ?? '');
                        $role = trim(strtolower($data[3] ?? 'faculty'));
                        $branch = trim($data[4] ?? null);

                        if (!$name || !$email) { $skipped++; $errors[]="Row {$row}: missing name/email"; continue; }
                        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $skipped++; $errors[]="Row {$row}: invalid email"; continue; }

                        $chk = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1"); $chk->execute([$email]);
                        if ($chk->fetch()) { $skipped++; $errors[]="Row {$row}: exists"; continue; }

                        if (empty($passwordRaw)) $passwordRaw = bin2hex(random_bytes(4));
                        $passHash = password_hash($passwordRaw, PASSWORD_DEFAULT);

                        $roleMap = ['class incharge'=>'incharge','class_incharge'=>'incharge'];
                        if (isset($roleMap[$role])) $role = $roleMap[$role];

                        $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role,branch,status,created_at) VALUES (?,?,?,?,?,'approved',NOW())");
                        $stmt->execute([$name,$email,$passHash,$role,$branch]);
                        $inserted++;
                    }
                    fclose($h);
                } else { $pdo->rollBack(); $error='Could not open file.'; }
                $pdo->commit();
                $msg = "Inserted: {$inserted}, Skipped: {$skipped}";
                $report = ['inserted'=>$inserted,'skipped'=>$skipped,'errors'=>$errors];
            } catch (Exception $e) {
                $pdo->rollBack(); $error = 'DB error: '.$e->getMessage();
            }
        }
    }
}
$token = csrf_token();
?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Upload Faculty (CSV)</h3>
    <a href="/college_project/admin/dashboard.php" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?=htmlspecialchars($msg)?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>

  <div class="card p-3">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($token)?>">
      <div class="mb-3">
        <label class="form-label">CSV (name,email,password,role,branch)</label>
        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
      </div>
      <button class="btn btn-primary">Upload</button>
    </form>
  </div>

  <?php if ($report): ?>
    <div class="card p-3 mt-3">
      <h6>Report</h6>
      <p>Inserted: <?= (int)$report['inserted'] ?> — Skipped: <?= (int)$report['skipped'] ?></p>
      <?php if (!empty($report['errors'])): ?>
        <div class="alert alert-warning"><ul><?php foreach($report['errors'] as $er): ?><li><?=htmlspecialchars($er)?></li><?php endforeach;?></ul></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
