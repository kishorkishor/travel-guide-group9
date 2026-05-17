<?php $u = $_SESSION['user']; ?>
<!DOCTYPE html>

<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Wishlist &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">

</head>
<body class="app-body">

<header class="navbar">
    <div class="navbar-inner">

        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">&#127758;</span>
            <span>TravelGuide</span>
        </a>
        <nav class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=profile">Profile</a>
            <a href="index.php?page=wishlist" class="active">Wishlist</a>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></span> 
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($u['name']) ?></span>
                    <span class="user-role"><?= htmlspecialchars($u['role']) ?></span> 
                </span>
            </span>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>

        </div>
    </div>

</header>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">&#10084; My Wishlist</h1>
            <p class="page-sub">Places you've saved for your future travels</p>
        </div>
        <span class="badge-count" id="wishlistCount"><?= count($items) ?> saved</span>

    </div>

    <div id="wishlistGrid">
        <?php if (empty($items)): ?>
            <div class="card" style="padding:48px;text-align:center;" id="emptyState">
                <div style="font-size:48px;margin-bottom:16px;">&#128203;</div>
                <p style="color:var(--text-muted);">Your wishlist is empty. Browse destinations and save your favorites!</p>
                <a href="index.php?page=home" class="btn btn-primary" style="margin-top:16px;">Explore Destinations</a>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($items as $item): ?>
                    <div class="post-card" id="wishlist-item-<?= $item['post_id'] ?>">
                        <div class="post-card-img">
                            <?php if (!empty($item['image'])): ?>

                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                            <?php else: ?>
                                &#127748;

                            <?php endif; ?>
                        </div>
                        <div class="post-card-body">
                            <h3><?= htmlspecialchars($item['title']) ?></h3>
                            <div class="post-card-meta">
                                <span class="badge badge-info"><?= htmlspecialchars($item['country']) ?></span>
                                <span class="badge badge-primary"><?= htmlspecialchars($item['genre']) ?></span>
                                <?php
                                    $costClass = $item['cost_level'] === 'low' ? 'badge-success' : ($item['cost_level'] === 'medium' ? 'badge-warning' : 'badge-danger');
                                ?>
                                <span class="badge <?= $costClass ?>"><?= ucfirst($item['cost_level']) ?> Cost</span>
                            </div>
                        </div>

                        <div class="post-card-footer">
                            <small style="color:var(--text-muted);">Saved <?= date('M d, Y', strtotime($item['added_at'])) ?></small>
                            <button class="btn-sm btn-delete" onclick="removeWishlist(<?= $item['post_id'] ?>)">
                                &#10005; Remove 
                            </button>
                        </div>
                    </div> 
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

<script>

function removeWishlist(postId) {
    if (!confirm('Remove this place from your wishlist?')) return;

    var formData = new FormData();
    formData.append('post_id', postId);

    fetch('index.php?page=ajax&type=wishlist_remove', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            var el = document.getElementById('wishlist-item-' + postId);
            if (el) {
                el.style.transition = 'opacity .3s, transform .3s';
                el.style.opacity = '0';
                el.style.transform = 'scale(0.95)';
                setTimeout(function () { el.remove(); updateCount(); }, 300);
            }

        } else {
            alert(data.error || 'Failed to remove from wishlist.');

        }
    })
    .catch(function (e) { console.error(e); });
}

function updateCount() {
    var cards = document.querySelectorAll('.post-card');
    var counter = document.getElementById('wishlistCount');
    counter.textContent = cards.length + ' saved';
    if (cards.length === 0) {
        document.getElementById('wishlistGrid').innerHTML =
            '<div class="card" style="padding:48px;text-align:center;">' +
            '<div style="font-size:48px;margin-bottom:16px;">&#128203;</div>' +
            '<p style="color:var(--text-muted);">Your wishlist is empty. Browse destinations and save your favorites!</p>' +
            '<a href="index.php?page=home" class="btn btn-primary" style="margin-top:16px;">Explore Destinations</a></div>';
    } 
}
</script>

</body>

</html>
