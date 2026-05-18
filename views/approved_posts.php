<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Approved Posts &mdash; Travel Guide</title>
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
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="index.php?page=create_request">New Request</a>
            <a href="index.php?page=my_requests">My Requests</a>
            <a href="index.php?page=approved_posts" class="active">Approved Posts</a>
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
            <h1 class="page-title">My Published Posts</h1>
            <p class="page-sub">Destinations you submitted that have been approved and published</p>
        </div>
    </div>

    <?php if (empty($posts)): ?>
        <div class="card" style="padding:48px;text-align:center;">
            <div style="font-size:48px;margin-bottom:16px;">&#128214;</div>
            <p style="color:var(--text-muted);">None of your posts have been approved yet. Keep submitting!</p>
            <a href="index.php?page=create_request" class="btn btn-primary" style="margin-top:16px;">Submit New Request</a>
        </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <div class="post-card-img">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                        <?php else: ?>
                            &#127748;
                        <?php endif; ?>
                    </div>
                    <div class="post-card-body">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <div class="post-card-meta">
                            <span class="badge badge-info"><?= htmlspecialchars($post['country']) ?></span>
                            <span class="badge badge-primary"><?= htmlspecialchars($post['genre']) ?></span>
                            <span class="badge badge-success">Published</span>
                        </div>
                        <p><?= htmlspecialchars(mb_substr($post['short_history'], 0, 120)) ?>...</p>
                    </div>
                    <div class="post-card-footer">
                        <small style="color:var(--text-muted);"><?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                        <button class="btn-sm btn-edit" onclick="requestChanges(<?= $post['id'] ?>)">Request Changes</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

<script>
function requestChanges(postId) {
    if (confirm('Create a change request for this published post? You will be redirected to a pre-filled form.')) {
        window.location.href = 'index.php?page=create_request&change_for=' + postId;
    }
}
</script>

</body>
</html>
