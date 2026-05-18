
<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Requests &mdash; Travel Guide</title>
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
            <a href="index.php?page=my_requests" class="active">My Requests</a>
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
            <h1 class="page-title">My Post Requests</h1>
            <p class="page-sub">Track and manage your submitted destination requests</p>
        </div>
        <a href="index.php?page=create_request" class="btn btn-primary">+ New Request</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['created' => 'Post request submitted successfully!', 'updated' => 'Request updated successfully!', 'deleted' => 'Request deleted.']; ?>
        <?php if (isset($msgs[$_GET['msg']])): ?>
            <div class="alert alert-success"><?= $msgs[$_GET['msg']] ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="searchInput" class="search-input" placeholder="Search requests by title or country...">
            </div>
            <span class="badge-count" id="resultCount"><?= count($requests) ?> total</span>
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
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="8" class="empty">No requests yet. Submit your first one!</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $i => $req): ?>
                            <?php $data = json_decode($req['post_data'], true); ?>
                            <tr id="row-<?= $req['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($data['title'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($data['country'] ?? 'N/A') ?></td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($data['genre'] ?? '') ?></span></td>
                                <td>
                                    <?php
                                        $cl = $data['cost_level'] ?? '';
                                        $cc = $cl === 'low' ? 'badge-success' : ($cl === 'medium' ? 'badge-warning' : 'badge-danger');
                                    ?>
                                    <span class="badge <?= $cc ?>"><?= ucfirst($cl) ?></span>
                                </td>
                                <td>
                                    <?php
                                        $sc = $req['status'] === 'pending' ? 'badge-warning' : ($req['status'] === 'approved' ? 'badge-success' : 'badge-danger');
                                    ?>
                                    <span class="badge <?= $sc ?>"><?= ucfirst($req['status']) ?></span>
                                </td>
                                <td><?= date('M d, Y', strtotime($req['requested_at'])) ?></td>
                                <td class="text-right">
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a class="btn-sm btn-edit" href="index.php?page=edit_request&id=<?= $req['id'] ?>">Edit</a>
                                        <button class="btn-sm btn-delete" onclick="deleteRequest(<?= $req['id'] ?>)">Delete</button>
                                    <?php else: ?>
                                        <span style="color:var(--text-light);font-size:12px;">--</span>
                                    <?php endif; ?>
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

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function statusBadge(s) {
        var cls = s === 'pending' ? 'badge-warning' : (s === 'approved' ? 'badge-success' : 'badge-danger');
        return '<span class="badge ' + cls + '">' + s.charAt(0).toUpperCase() + s.slice(1) + '</span>';
    }

    function costBadge(c) {
        var cls = c === 'low' ? 'badge-success' : (c === 'medium' ? 'badge-warning' : 'badge-danger');
        return '<span class="badge ' + cls + '">' + c.charAt(0).toUpperCase() + c.slice(1) + '</span>';
    }

    function render(rows) {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="8" class="empty">No matching results.</td></tr>';
            counter.textContent = '0 results';
            return;
        }
        var html = '';
        rows.forEach(function (r, i) {
            var actions = r.status === 'pending'
                ? '<a class="btn-sm btn-edit" href="index.php?page=edit_request&id=' + r.id + '">Edit</a><button class="btn-sm btn-delete" onclick="deleteRequest(' + r.id + ')">Delete</button>'
                : '<span style="color:var(--text-light);font-size:12px;">--</span>';
            html += '<tr id="row-' + r.id + '">' +
                '<td>' + (i+1) + '</td>' +
                '<td>' + esc(r.title) + '</td>' +
                '<td>' + esc(r.country) + '</td>' +
                '<td><span class="badge badge-primary">' + esc(r.genre) + '</span></td>' +
                '<td>' + costBadge(r.cost_level || '') + '</td>' +
                '<td>' + statusBadge(r.status) + '</td>' +
                '<td>' + esc(r.requested_at) + '</td>' +
                '<td class="text-right">' + actions + '</td></tr>';
        });
        body.innerHTML = html;
        counter.textContent = rows.length + (input.value.trim() ? ' results' : ' total');
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('index.php?page=ajax&type=search_requests&q=' + encodeURIComponent(input.value.trim()),
                  { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function (e) { console.error(e); });
        }, 200);
    });
})();

function deleteRequest(id) {
    if (!confirm('Delete this request? This cannot be undone.')) return;

    var formData = new FormData();
    formData.append('id', id);

    fetch('index.php?page=ajax&type=delete_request', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            var row = document.getElementById('row-' + id);
            if (row) row.remove();
        } else {
            alert(data.error || 'Failed to delete request.');
        }
    })
    .catch(function (e) { console.error(e); });
}
</script>

</body>
</html>
