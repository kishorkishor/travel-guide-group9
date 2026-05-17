<?php $user = $_SESSION['user']; $isEdit = !empty($editing); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Moderation &mdash; Travel Guide</title>
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
            <a href="index.php?page=post_moderation" class="active">Posts</a>
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
            <h1 class="page-title">Post Moderation</h1>
            <p class="page-sub">Review pending requests, manage published posts</p>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['updated' => 'Post updated successfully.', 'deleted' => 'Post deleted successfully.']; ?>
        <?php if (isset($msgs[$_GET['msg']])): ?>
            <div class="alert alert-success"><?= $msgs[$_GET['msg']] ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($isEdit): ?>
    <!-- ============ Edit Post Form ============ -->
    <div class="card form-card">
        <h3 class="card-title">&#9998; Edit Post #<?= intval($editing['id']) ?></h3>
        <form method="POST" action="index.php?page=post_moderation&action=update&id=<?= intval($editing['id']) ?>" class="form" novalidate>
            <div class="field-row">
                <div class="field">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($editing['country'] ?? '') ?>" required>
                </div>
            </div>
            <div class="field">
                <label for="short_history">Short History</label>
                <textarea id="short_history" name="short_history" rows="3" required><?= htmlspecialchars($editing['short_history'] ?? '') ?></textarea>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="genre">Genre</label>
                    <select id="genre" name="genre" required>
                        <?php foreach (['beach','mountain','city','historical','adventure','cultural','nature'] as $g): ?>
                            <option value="<?= $g ?>" <?= ($editing['genre'] ?? '') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="cost_level">Cost Level</label>
                    <select id="cost_level" name="cost_level" required>
                        <?php foreach (['low','medium','high'] as $cl): ?>
                            <option value="<?= $cl ?>" <?= ($editing['cost_level'] ?? '') === $cl ? 'selected' : '' ?>><?= ucfirst($cl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="field">
                <label for="travel_medium_info">Travel Medium Info</label>
                <textarea id="travel_medium_info" name="travel_medium_info" rows="2" required><?= htmlspecialchars($editing['travel_medium_info'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
                <a href="index.php?page=post_moderation" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Post</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ============ Pending Requests ============ -->
    <div class="card">
        <div class="card-toolbar">
            <h3 style="font-size:16px;font-weight:600;margin:0;">Pending Requests</h3>
            <span class="badge-count"><?= count($pendingRequests) ?> pending</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Scout</th>
                        <th>Title</th>
                        <th>Country</th>
                        <th>Genre</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="pendingBody">
                    <?php if (empty($pendingRequests)): ?>
                        <tr><td colspan="8" class="empty">No pending requests.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pendingRequests as $i => $req): ?>
                            <?php $data = json_decode($req['post_data'], true); ?>
                            <tr id="req-row-<?= $req['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($req['scout_name']) ?></td>
                                <td><?= htmlspecialchars($data['title'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($data['country'] ?? '') ?></td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($data['genre'] ?? '') ?></span></td>
                                <td>
                                    <?php if (!empty($req['original_post_id'])): ?>
                                        <span class="badge badge-warning">Change</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">New</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d', strtotime($req['requested_at'])) ?></td>
                                <td class="text-right">
                                    <button class="btn-sm btn-success" onclick="approveRequest(<?= $req['id'] ?>)">Approve</button>
                                    <button class="btn-sm btn-delete" onclick="rejectRequest(<?= $req['id'] ?>)">Reject</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============ Published Posts ============ -->
    <div class="card">
        <div class="card-toolbar">
            <h3 style="font-size:16px;font-weight:600;margin:0;">Published Posts</h3>
            <span class="badge-count"><?= count($posts) ?> posts</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Country</th>
                        <th>Genre</th>
                        <th>Cost</th>
                        <th>Scout</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr><td colspan="8" class="empty">No posts yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($posts as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($p['title']) ?></td>
                                <td><?= htmlspecialchars($p['country']) ?></td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($p['genre']) ?></span></td>
                                <td>
                                    <?php $cc = $p['cost_level'] === 'low' ? 'badge-success' : ($p['cost_level'] === 'medium' ? 'badge-warning' : 'badge-danger'); ?>
                                    <span class="badge <?= $cc ?>"><?= ucfirst($p['cost_level']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($p['scout_name']) ?></td>
                                <td><span class="badge badge-success">Published</span></td>
                                <td class="text-right">
                                    <a class="btn-sm btn-edit" href="index.php?page=post_moderation&action=edit&id=<?= $p['id'] ?>">Edit</a>
                                    <a class="btn-sm btn-delete" href="index.php?page=post_moderation&action=delete&id=<?= $p['id'] ?>" onclick="return confirm('Delete this post and all its comments?')">Delete</a>
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

<!-- AJAX Approve / Reject -->
<script>
function approveRequest(id) {
    if (!confirm('Approve this request and publish the post?')) return;
    var fd = new FormData(); fd.append('id', id);
    fetch('index.php?page=ajax&type=approve_request', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) { if (d.success) { var row = document.getElementById('req-row-' + id); if (row) row.remove(); location.reload(); } else { alert('Failed to approve.'); } });
}

function rejectRequest(id) {
    if (!confirm('Reject this request?')) return;
    var fd = new FormData(); fd.append('id', id);
    fetch('index.php?page=ajax&type=reject_request', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) { if (d.success) { var row = document.getElementById('req-row-' + id); if (row) row.remove(); } else { alert('Failed to reject.'); } });
}
</script>

</body>
</html>
