<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND password=? AND status='approved'");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];

        header("Location: {$user['role']}/dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials or account not approved.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Campus Connect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #0f172a;
        color: #fff;
    }
    .login-container {
        min-height: 100vh;
        display: flex;
    }
    .left-panel {
        background: url('assets/login-bg.jpg') center/cover no-repeat;
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
    .login-card {
        background-color: #1e293b;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        width: 100%;
        max-width: 380px;
    }
    .btn-purple {
        background: linear-gradient(90deg, #7c3aed, #9333ea);
        color: #fff;
        border: none;
    }
    .btn-purple:hover {
        background: linear-gradient(90deg, #6d28d9, #7e22ce);
    }
    .form-control {
        background-color: #0f172a;
        border: 1px solid #334155;
        color: #fff;
    }
    .form-control:focus {
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
</head>
<body>

<div class="login-container">
    <!-- Left Image Panel -->
    <div class="left-panel">
        <div class="left-overlay">
            <h1>Welcome Back to Campus Connect</h1>
        </div>
    </div>

    <!-- Right Login Form -->
    <div class="right-panel">
        <div class="login-card">
            <h3 class="mb-4 text-center">Login</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-purple w-100 py-2">Login</button>
            </form>
            <div class="mt-3 text-center">
                New here? <a href="register.php">Register Now</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
