<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comment Moderation &mdash; Travel Guide</title>
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
            <a href="index.php?page=users">Users</a>
            <a href="index.php?page=post_moderation">Posts</a>
            <a href="index.php?page=comment_moderation" class="active">Comments</a>
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
            <h1 class="page-title">Comment Moderation</h1>
            <p class="page-sub">Review and manage user comments across all posts</p>
        </div>
    </div>

    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="searchInput" class="search-input" placeholder="Search comments by content, user, or post...">
            </div>
            <span class="badge-count" id="resultCount"><?= count($comments) ?> total</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Post</th>
                        <th>User</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($comments)): ?>
                        <tr><td colspan="6" class="empty">No comments yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($comments as $i => $c): ?>
                            <tr id="comment-row-<?= $c['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars(mb_substr($c['post_title'], 0, 30)) ?></td>
                                <td><?= htmlspecialchars($c['user_name']) ?></td>
                                <td><?= htmlspecialchars(mb_substr($c['content'], 0, 80)) ?><?= mb_strlen($c['content']) > 80 ? '...' : '' ?></td>
                                <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                <td class="text-right">
                                    <button class="btn-sm btn-delete" onclick="deleteComment(<?= $c['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

<!-- AJAX Search & Delete -->
<script>
(function () {
    var input   = document.getElementById('searchInput');
    var body    = document.getElementById('tableBody');
    var counter = document.getElementById('resultCount');
    var timer;

    function esc(s) { return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function render(rows) {
        if (!rows.length) { body.innerHTML = '<tr><td colspan="6" class="empty">No matching comments.</td></tr>'; counter.textContent = '0 results'; return; }
        var html = '';
        rows.forEach(function (c, i) {
            var content = c.content.length > 80 ? esc(c.content.substring(0, 80)) + '...' : esc(c.content);
            html += '<tr id="comment-row-' + c.id + '"><td>' + (i+1) + '</td><td>' + esc(c.post_title) + '</td><td>' + esc(c.user_name) + '</td><td>' + content + '</td><td>' + esc(c.created_at) + '</td><td class="text-right"><button class="btn-sm btn-delete" onclick="deleteComment(' + c.id + ')">Delete</button></td></tr>';
        });
        body.innerHTML = html;
        counter.textContent = rows.length + (input.value.trim() ? ' results' : ' total');
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('index.php?page=ajax&type=search_comments&q=' + encodeURIComponent(input.value.trim()), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); }).then(render).catch(function (e) { console.error(e); });
        }, 200);
    });
})();

function deleteComment(id) {
    if (!confirm('Delete this comment?')) return;
    var fd = new FormData(); fd.append('id', id);
    fetch('index.php?page=ajax&type=delete_comment', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) { if (d.success) { var row = document.getElementById('comment-row-' + id); if (row) row.remove(); } });
}
</script>

</body>
</html>
