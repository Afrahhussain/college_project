<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $role = $_POST['role'];
    $branch = $_POST['branch'] ?? null;
    $year = $_POST['year'] ?? null;
    $section = $_POST['section'] ?? null;

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "Email already registered.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, branch, year, section, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $email, $password, $role, $branch, $year, $section, 'pending']);
        $msg = "Registration successful! Await admin approval.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Campus Connect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #0f172a;
        color: #fff;
    }
    .register-container {
        min-height: 100vh;
        display: flex;
    }
    .left-panel {
        background: url('assets/register-bg.jpg') center/cover no-repeat;
        flex: 1;
        position: relative;
    }
    .left-overlay {
        background-color: rgba(15, 23, 42, 0.7);
        position: absolute;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        text-align: center;
    }
    .left-overlay h1 {
        font-size: 2.2rem;
        font-weight: 600;
    }
    .right-panel {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    .register-card {
        background-color: #1e293b;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        width: 100%;
        max-width: 420px;
    }
    .btn-purple {
        background: linear-gradient(90deg, #7c3aed, #9333ea);
        color: #fff;
        border: none;
    }
    .btn-purple:hover {
        background: linear-gradient(90deg, #6d28d9, #7e22ce);
    }
    .form-control, .form-select {
        background-color: #0f172a;
        border: 1px solid #334155;
        color: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
    }
    a {
        color: #a78bfa;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
<script>
function toggleExtraFields() {
    var role = document.getElementById("role").value;
    var extraFields = document.getElementById("studentFields");
    extraFields.style.display = (role === "student") ? "block" : "none";
}
</script>
</head>
<body>

<div class="register-container">
    <!-- Left Image Panel -->
    <div class="left-panel">
        <div class="left-overlay">
            <h1>Join Campus Connect</h1>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="right-panel">
        <div class="register-card">
            <h3 class="mb-4 text-center">Create Account</h3>
            <?php if ($msg): ?>
                <div class="alert alert-success py-2"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" id="role" class="form-select" required onchange="toggleExtraFields()">
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="class incharge">Class Incharge</option>
                        <option value="hod">HOD</option>
                    </select>
                </div>
                <div id="studentFields" style="display:none;">
                    <div class="mb-3">
                        <label>Branch</label>
                        <select name="branch" class="form-select">
                            <option value="">Branch</option>
                            <option value="CSE">CSE</option>
                            <option value="ECE">ECE</option>
                            <option value="EEE">EEE</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Year</label>
                        <select name="year" class="form-select">
                            <option value="">Year</option>
                            <option value="1">1st</option>
                            <option value="2">2nd</option>
                            <option value="3">3rd</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Section</label>
                        <select name="section" class="form-select">
                            <option value="">Section</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-purple w-100 py-2">Register</button>
            </form>
            <div class="mt-3 text-center">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
