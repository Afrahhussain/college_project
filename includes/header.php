<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';

// set your project base URL here (change if folder name differs)
$BASE_URL = '/college_project'; // <-- update this if different

$user_name = $_SESSION['user_name'] ?? ($_SESSION['name'] ?? 'Guest');
$user_role = $_SESSION['role'] ?? null;

// determine current path for active link highlighting
$current_uri = $_SERVER['REQUEST_URI'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Campus Connect</title>

  <!-- Bootstrap CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --sidebar-bg: #243644;
      --sidebar-accent: #1a2a36;
      --brand: #0d6efd;
    }
    body { background:#f4f6f9; min-height:100vh; }
    /* Navbar */
    .topbar {
      background: linear-gradient(90deg,#243644,#1b2f3b);
      color: #fff;
      height: 56px;
      display:flex; align-items:center;
      padding:0 16px;
      position:fixed; top:0; left:0; right:0; z-index:1040;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .brand { font-weight:600; font-size:1.1rem; color:white; margin-left:12px; }
    /* Sidebar */
    .app-sidebar {
      position:fixed; top:56px; left:-260px; height:calc(100vh - 56px);
      width:260px; background:var(--sidebar-bg); color:#eee; transition:left .28s;
      z-index:1030; overflow:auto; padding-top:10px;
    }
    .app-sidebar.active { left:0; }
    .app-sidebar .nav-link { color: #dbe6ee; padding:12px 18px; }
    .app-sidebar .nav-link:hover { background: rgba(255,255,255,0.04); color:#fff; }
    .app-sidebar .nav-link.active { background: rgba(255,255,255,0.06); color:#fff; }
    .main-wrap { margin-top:56px; transition:margin-left .28s; padding:24px; }
    .main-wrap.shift { margin-left:260px; }
    .sidebar-footer { position:sticky; bottom:0; padding:12px 18px; background: linear-gradient(180deg,transparent, rgba(0,0,0,0.03)); }
    /* Cards & table polish */
    .stat-card { border-radius:10px; box-shadow:0 6px 20px rgba(14,30,37,0.06); padding:18px; color:#fff; }
    .table-card { padding:0; overflow:hidden; border-radius:10px; box-shadow:0 6px 20px rgba(14,30,37,0.06); }
    .search-form .form-control { min-width: 220px; }
    /* overlay for mobile */
    .sidebar-overlay { display:none; position:fixed; inset:56px 0 0 0; background:rgba(0,0,0,0.35); z-index:1025; }
    .sidebar-overlay.active { display:block; }
    @media (max-width: 991px) {
      .main-wrap.shift { margin-left:0; } /* don't push content on mobile */
    }
  </style>
</head>
<body>
  <div class="topbar d-flex align-items-center">
    <button id="sidebarToggle" class="btn btn-outline-light btn-sm" title="Toggle menu">
      <i class="bi bi-list"></i>
    </button>
    <div class="brand ms-2">Campus Connect</div>

    <div class="ms-auto d-flex align-items-center gap-3">
      <div class="d-none d-md-block">
        <!-- quick search (submits to manage users page) -->
        <form class="d-flex search-form" method="get" action="<?= $BASE_URL ?>/admin/manage_users.php">
          <input class="form-control form-control-sm me-1" name="search" placeholder="Search users by name/email" />
          <button class="btn btn-sm btn-light" type="submit"><i class="bi bi-search"></i></button>
        </form>
      </div>

      <div class="dropdown">
        <a class="btn btn-sm btn-outline-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user_name ?? 'Guest') ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= $BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="app-sidebar" id="appSidebar" aria-hidden="true">
    <nav class="nav flex-column px-1">
      <?php
      // convenience helper for active link
      function _is_active($uri_fragment) {
        global $current_uri;
        return strpos($current_uri, $uri_fragment) !== false ? 'active' : '';
      }
      $menu = [
        ['url'=> $BASE_URL.'/admin/dashboard.php', 'icon'=>'bi-speedometer2','label'=>'Dashboard','roles'=>['admin']],
        ['url'=> $BASE_URL.'/admin/manage_users.php', 'icon'=>'bi-people','label'=>'Manage Users','roles'=>['admin']],
        ['url'=> $BASE_URL.'/admin/upload_students_csv.php', 'icon'=>'bi-upload','label'=>'Upload Students','roles'=>['admin']],
        ['url'=> $BASE_URL.'/admin/upload_faculty_csv.php', 'icon'=>'bi-person-badge','label'=>'Upload Faculty','roles'=>['admin']],
        ['url'=> $BASE_URL.'/admin/class_allotment.php','icon'=>'bi-grid-1x2','label'=>'Class Allotments','roles'=>['admin']],
        ['url'=> $BASE_URL.'/admin/upload_marks.php','icon'=>'bi-journal-check','label'=>'Upload Marks','roles'=>['admin','faculty']],
        ['url'=> $BASE_URL.'/admin/upload_attendance.php','icon'=>'bi-calendar-check','label'=>'Upload Attendance','roles'=>['admin','faculty']],
      ];
      foreach ($menu as $item):
        if ($user_role && !in_array($user_role, $item['roles']) && !in_array('faculty',$item['roles'])) {
          // simple role filter: admin sees admin items; faculty will have its own pages later
        }
        // show if user role matches or if it's admin (we keep for admin)
      ?>
        <?php if ($user_role === 'admin'): ?>
          <a href="<?= $item['url'] ?>" class="nav-link <?= _is_active($item['url']) ?>">
            <i class="bi <?= $item['icon'] ?> me-2"></i> <?= $item['label'] ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
      <small class="text-muted d-block">Logged in as <strong><?= htmlspecialchars($user_role ?? 'Guest') ?></strong></small>
    </div>
  </div>

  <div id="sidebarOverlay" class="sidebar-overlay"></div>

  <main class="main-wrap" id="mainWrap">
