<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard &mdash; Travel Guide</title>
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
            <a href="index.php?page=users">Users</a>
            <a href="index.php?page=post_moderation">Posts</a>
            <a href="index.php?page=comment_moderation">Comments</a>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-role">Admin</span>
                </span>
            </span>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Admin Dashboard</h1>
            <p class="page-sub">Platform overview and management</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $counts['total_users'] ?></div>
            <div class="stat-label">Total Users</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:8px;">
                <?= $counts['admins'] ?> admins &middot; <?= $counts['scouts'] ?> scouts &middot; <?= $counts['users'] ?> users
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:var(--warning);"><?= $counts['pending_requests'] ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:var(--success);"><?= $counts['total_posts'] ?></div>
            <div class="stat-label">Published Posts</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#0ea5e9;"><?= $counts['total_comments'] ?></div>
            <div class="stat-label">Total Comments</div>
        </div>
    </div>

    <div class="card" style="padding:32px;text-align:center;">
        <h3 style="margin-bottom:12px;">Quick Actions</h3>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="index.php?page=users" class="btn btn-primary">Manage Users</a>
            <a href="index.php?page=post_moderation" class="btn btn-ghost">Moderate Posts</a>
            <a href="index.php?page=comment_moderation" class="btn btn-ghost">Review Comments</a>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

</body>
</html>
