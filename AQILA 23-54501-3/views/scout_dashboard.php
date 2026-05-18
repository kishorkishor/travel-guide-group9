<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scout Dashboard &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=dashboard">
            <span class="brand-icon">&#127758;</span>
            <span>TravelGuide</span>
        </a>
        <nav class="nav-links">
            <a href="index.php?page=dashboard" class="active">Dashboard</a>
            <a href="index.php?page=create_request">New Request</a>
            <a href="index.php?page=my_requests">My Requests</a>
            <a href="index.php?page=approved_posts">Approved Posts</a>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-role">Scout</span>
                </span>
            </span>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Scout Dashboard</h1>
            <p class="page-sub">Overview of your post request submissions</p>
        </div>
        <a href="index.php?page=create_request" class="btn btn-primary">+ New Request</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $counts['total'] ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:var(--warning);"><?= $counts['pending'] ?></div>
            <div class="stat-label">Pending Review</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:var(--success);"><?= $counts['approved'] ?></div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:var(--error);"><?= $counts['rejected'] ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    <div class="card" style="padding:32px;text-align:center;">
        <h3 style="margin-bottom:12px;">Quick Actions</h3>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="index.php?page=create_request" class="btn btn-primary">Submit New Destination</a>
            <a href="index.php?page=my_requests" class="btn btn-ghost">View My Requests</a>
            <a href="index.php?page=approved_posts" class="btn btn-ghost">View Published Posts</a>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

</body>
</html>
