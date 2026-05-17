<?php $isLoggedIn = isset($_SESSION['user']); ?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>

<?php if (!$isLoggedIn): ?>

<body class="auth-body">

<div style="position:relative;z-index:1;text-align:center;color:#fff;max-width:700px;">
    <div style="font-size:64px;margin-bottom:24px;">&#127758;</div>
    <h1 style="font-size:48px;font-weight:700;margin-bottom:16px;">Travel Guide</h1>
    <p style="font-size:18px;opacity:.9;margin-bottom:40px;">
        Discover amazing destinations around the world. Get travel suggestions, cost estimates, and plan your next adventure.
    </p>
    <div class="hero-actions">
        <a href="index.php?page=login" class="btn btn-primary" style="padding:14px 32px;font-size:16px;">Sign In</a> 
        <a href="index.php?page=register" class="btn btn-ghost" style="padding:14px 32px;font-size:16px;background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.3);">Register</a>
    </div>
</div>
</body>

<?php else: ?>

<?php $user = $_SESSION['user']; ?>
<body class="app-body">

<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">&#127758;</span>
            <span>TravelGuide</span>
        </a>
        <nav class="nav-links">
            <a href="index.php?page=home" class="active">Home</a>
            <a href="index.php?page=profile">Profile</a>
            <?php if ($user['role'] === 'user' && $user['is_verified'] == 1): ?>
                <a href="index.php?page=wishlist">Wishlist</a>
            <?php endif; ?>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-role"><?= htmlspecialchars($user['role']) ?></span>
                </span>

            </span>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
    <?php if ($user['is_verified'] != 1): ?>
        
        <div class="alert alert-warning">
            &#9888; Your account is pending admin approval. You will have full access once verified.
        </div>
        <div class="hero">

            <div style="font-size:64px;margin-bottom:16px;">&#9203;</div> 

            <h1>Almost There!</h1>
            <p>Your account has been created but is awaiting verification by an administrator. Once approved, you'll be able to explore all destinations and features.</p>
        </div>
    <?php else: ?>
        
        <div class="page-header">
            <div>

                <h1 class="page-title">Explore Destinations</h1>
                <p class="page-sub">Discover the latest travel recommendations from our scouts</p>
            </div>

        </div>

        <?php if (empty($posts)): ?>

            <div class="card" style="padding:48px;text-align:center;">
                <div style="font-size:48px;margin-bottom:16px;">&#127757;</div>
                <p style="color:var(--text-muted);">No posts published yet. Check back soon!</p>
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
                                <?php
                                    $costClass = $post['cost_level'] === 'low' ? 'badge-success' : ($post['cost_level'] === 'medium' ? 'badge-warning' : 'badge-danger');
                                ?> 
                                <span class="badge <?= $costClass ?>"><?= ucfirst($post['cost_level']) ?> Cost</span>
                            </div>
                            <p><?= htmlspecialchars(mb_substr($post['short_history'], 0, 120)) ?>...</p>
                        </div>
                        <?php if ($user['role'] === 'user'): ?>
                            <div class="post-card-footer">
                                <small style="color:var(--text-muted);">By <?= htmlspecialchars($post['scout_name']) ?></small>
                                <button class="btn-wishlist <?= !empty($post['in_wishlist']) ? 'active' : '' ?>"

                                        data-post-id="<?= $post['id'] ?>"
                                        onclick="toggleWishlist(this)">
                                    <?= !empty($post['in_wishlist']) ? '&#10084; Saved' : '&#9825; Save' ?>

                                </button>
                            </div>
                        <?php else: ?>
                            <div class="post-card-footer">

                                <small style="color:var(--text-muted);">By <?= htmlspecialchars($post['scout_name']) ?></small>
                                <span></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?> 

            </div>
        <?php endif; ?> 
    <?php endif; ?>

</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

<script> 
function toggleWishlist(btn) {
    var postId = btn.getAttribute('data-post-id');
    var isActive = btn.classList.contains('active');
    var type = isActive ? 'wishlist_remove' : 'wishlist_add';

    var formData = new FormData();
    formData.append('post_id', postId);

    fetch('index.php?page=ajax&type=' + type, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(function (r) { return r.json(); })

    .then(function (data) {
        if (data.success) {
            btn.classList.toggle('active');
            btn.innerHTML = btn.classList.contains('active') ? '&#10084; Saved' : '&#9825; Save';
        } else {
            alert(data.error || 'Failed to update wishlist.');
        }
    })
    .catch(function (e) { console.error(e); });
} 
</script>

</body>
<?php endif; ?>
</html>
