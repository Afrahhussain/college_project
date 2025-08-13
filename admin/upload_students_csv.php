<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../includes/header.php';

$msg=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['csv_file'])) {
    $tok = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($tok)) { $error='Invalid request token.'; }
    else {
      $f = $_FILES['csv_file'];
      if ($f['error']===0 && $f['size']>0) {
        $tmp = $f['tmp_name'];
        if (($h = fopen($tmp,'r')) !== false) {
          $row=0; $inserted=0; $skipped=0;
          $pdo->beginTransaction();
          while (($data = fgetcsv($h,1000,',')) !== false) {
            if ($row===0) { $row++; continue; }
            $row++;
            $name = trim($data[0] ?? '');
            $email = trim($data[1] ?? '');
            $password = password_hash(trim($data[2] ?? '1234'), PASSWORD_DEFAULT);
            $role = $data[3] ?? 'student';
            $branch = $data[4] ?? null;
            $year = $data[5] ?? null;
            $section = $data[6] ?? null;
            if (!$name || !$email) { $skipped++; continue; }
            // check duplicate
            $exists = $pdo->prepare("SELECT id FROM users WHERE email=?")->execute([$email]);
            $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $check->execute([$email]);
            if ($check->fetch()) { $skipped++; continue; }
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role,branch,year,section,status) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$name,$email,$password,$role,$branch,$year,$section,'approved']);
            $inserted++;
          }
          $pdo->commit();
          fclose($h);
          $msg = "Inserted: $inserted, Skipped: $skipped";
        } else $error='Could not open uploaded file.';
      } else $error='Upload error or empty file.';
    }
}

$token = csrf_token();
?>
<div class="container">
  <h3>Upload Students (CSV)</h3>
  <?php if($msg): ?><div class="alert alert-success"><?=$msg?></div><?php endif; ?>
  <?php if($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <div class="card p-3">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($token)?>">
      <div class="mb-3">
        <label class="form-label">CSV file (name,email,password,role,branch,year,section)</label>
        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
      </div>
      <button class="btn btn-primary">Upload</button>
    </form>
    <div class="mt-3"><small>Example header: <code>name,email,password,role,branch,year,section</code></small></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
