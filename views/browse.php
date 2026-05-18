<?php $user = $_SESSION['user']; $isGeneralUser = ($user['role'] === 'user' && $user['is_verified'] == 1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse Destinations &mdash; Travel Guide</title>
<link rel="stylesheet" href="../style.css">
</head>
<body class="app-body">

<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=browse">
            <span class="brand-icon">&#127758;</span>
            <span>TravelGuide</span>
        </a>
        <nav class="nav-links">
            <a href="index.php?page=browse" class="active">Browse</a>
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
    <div class="page-header">
        <div>
            <h1 class="page-title">Explore Destinations</h1>
            <p class="page-sub">Search and filter travel posts from around the world</p>
        </div>
    </div>

    <!-- ============ Search & Filter Bar ============ -->
    <div class="card form-card" style="margin-bottom:24px;">
        <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;">
            <div class="field" style="flex:2;min-width:200px;">
                <label for="searchQ">Search</label>
                <input type="text" id="searchQ" class="search-input" placeholder="Search by title or country..." style="padding-left:14px;">
            </div>
            <div class="field" style="flex:1;min-width:150px;">
                <label for="filterCountry">Country</label>
                <select id="filterCountry">
                    <option value="">All Countries</option>
                    <?php foreach ($countries as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="flex:1;min-width:150px;">
                <label for="filterGenre">Genre</label>
                <select id="filterGenre">
                    <option value="">All Genres</option>
                    <?php foreach (['beach','mountain','city','historical','adventure','cultural','nature'] as $g): ?>
                        <option value="<?= $g ?>"><?= ucfirst($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="min-width:140px;">
                <label>Cost Level</label>
                <div style="display:flex;gap:8px;padding:8px 0;">
                    <label class="checkbox" style="font-size:12px;"><input type="radio" name="filterCost" value=""> All</label>
                    <label class="checkbox" style="font-size:12px;"><input type="radio" name="filterCost" value="low"> Low</label>
                    <label class="checkbox" style="font-size:12px;"><input type="radio" name="filterCost" value="medium"> Med</label>
                    <label class="checkbox" style="font-size:12px;"><input type="radio" name="filterCost" value="high"> High</label>
                </div>
            </div>
        </div>
        <div style="margin-top:8px;"><span class="badge-count" id="resultCount"><?= count($posts) ?> destinations</span></div>
    </div>

    <!-- ============ Posts Grid ============ -->
    <div class="posts-grid" id="postsGrid">
        <?php if (empty($posts)): ?>
            <div class="card" style="padding:48px;text-align:center;grid-column:1/-1;">
                <div style="font-size:48px;margin-bottom:16px;">&#127757;</div>
                <p style="color:var(--text-muted);">No destinations published yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <a href="index.php?page=post_detail&id=<?= $post['id'] ?>" class="post-card" style="text-decoration:none;color:inherit;">
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
                            <?php $cc = $post['cost_level'] === 'low' ? 'badge-success' : ($post['cost_level'] === 'medium' ? 'badge-warning' : 'badge-danger'); ?>
                            <span class="badge <?= $cc ?>"><?= ucfirst($post['cost_level']) ?></span>
                        </div>
                        <p><?= htmlspecialchars(mb_substr($post['short_history'], 0, 120)) ?>...</p>
                    </div>
                    <div class="post-card-footer">
                        <small style="color:var(--text-muted);">By <?= htmlspecialchars($post['scout_name']) ?></small>
                        <span class="btn-sm btn-edit">Read More</span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

<!-- AJAX Search & Filter -->
<script>
(function () {
    var searchInput    = document.getElementById('searchQ');
    var countrySelect  = document.getElementById('filterCountry');
    var genreSelect    = document.getElementById('filterGenre');
    var costRadios     = document.querySelectorAll('input[name="filterCost"]');
    var grid           = document.getElementById('postsGrid');
    var counter        = document.getElementById('resultCount');
    var timer;

    function esc(s) { return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function costBadge(c) {
        var cls = c === 'low' ? 'badge-success' : (c === 'medium' ? 'badge-warning' : 'badge-danger');
        return '<span class="badge ' + cls + '">' + c.charAt(0).toUpperCase() + c.slice(1) + '</span>';
    }

    function renderPosts(rows) {
        if (!rows.length) {
            grid.innerHTML = '<div class="card" style="padding:48px;text-align:center;grid-column:1/-1;"><div style="font-size:48px;margin-bottom:16px;">&#128270;</div><p style="color:var(--text-muted);">No destinations match your filters.</p></div>';
            counter.textContent = '0 destinations';
            return;
        }
        var html = '';
        rows.forEach(function (p) {
            var imgHtml = p.image ? '<img src="' + esc(p.image) + '" alt="' + esc(p.title) + '">' : '&#127748;';
            var snippet = p.short_history ? esc(p.short_history.substring(0, 120)) + '...' : '';
            html += '<a href="index.php?page=post_detail&id=' + p.id + '" class="post-card" style="text-decoration:none;color:inherit;">' +
                '<div class="post-card-img">' + imgHtml + '</div>' +
                '<div class="post-card-body">' +
                    '<h3>' + esc(p.title) + '</h3>' +
                    '<div class="post-card-meta">' +
                        '<span class="badge badge-info">' + esc(p.country) + '</span>' +
                        '<span class="badge badge-primary">' + esc(p.genre) + '</span>' +
                        costBadge(p.cost_level) +
                    '</div>' +
                    '<p>' + snippet + '</p>' +
                '</div>' +
                '<div class="post-card-footer">' +
                    '<small style="color:var(--text-muted);">By ' + esc(p.scout_name) + '</small>' +
                    '<span class="btn-sm btn-edit">Read More</span>' +
                '</div></a>';
        });
        grid.innerHTML = html;
        counter.textContent = rows.length + ' destinations';
    }

    function doSearch() {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var q = searchInput.value.trim();
            var country = countrySelect.value;
            var genre = genreSelect.value;
            var cost = '';
            costRadios.forEach(function (r) { if (r.checked) cost = r.value; });

            var url = 'index.php?page=ajax&type=search_posts&q=' + encodeURIComponent(q) +
                      '&country=' + encodeURIComponent(country) +
                      '&genre=' + encodeURIComponent(genre) +
                      '&cost_level=' + encodeURIComponent(cost);

            fetch(url, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(renderPosts)
                .catch(function (e) { console.error(e); });
        }, 200);
    }

    searchInput.addEventListener('input', doSearch);
    countrySelect.addEventListener('change', doSearch);
    genreSelect.addEventListener('change', doSearch);
    costRadios.forEach(function (r) { r.addEventListener('change', doSearch); });
})();
</script>

</body>
</html>
