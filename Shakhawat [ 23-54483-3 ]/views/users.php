<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management &mdash; Travel Guide</title>
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
            <a href="index.php?page=users" class="active">Users</a>
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
            <h1 class="page-title">User Management</h1>
            <p class="page-sub">Add, verify, and manage user accounts</p>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['added' => 'User added successfully.', 'deleted' => 'User deleted successfully.']; ?>
        <?php if (isset($msgs[$_GET['msg']])): ?>
            <div class="alert alert-success"><?= $msgs[$_GET['msg']] ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ============ Add User Form ============ -->
    <div class="card form-card">
        <h3 class="card-title">+ Add New User</h3>
        <form method="POST" action="index.php?page=users&action=add" class="form" novalidate id="addUserForm">
            <div class="field-row">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Full name" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Email address" required>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min 6 characters" required>
                </div>
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="user">General User</option>
                        <option value="scout">Scout</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <label class="checkbox">
                <input type="checkbox" name="is_verified" value="1" checked>
                <span>Set as verified immediately</span>
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>

    <!-- ============ Users Table ============ -->
    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="searchInput" class="search-input" placeholder="Search users by name or email...">
            </div>
            <span class="badge-count" id="resultCount"><?= count($users) ?> total</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Verified</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="empty">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $i => $u): ?>
                            <tr id="user-row-<?= $u['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php
                                        $rc = $u['role'] === 'admin' ? 'badge-danger' : ($u['role'] === 'scout' ? 'badge-info' : 'badge-primary');
                                    ?>
                                    <span class="badge <?= $rc ?>"><?= ucfirst($u['role']) ?></span>
                                </td>
                                <td>
                                    <button class="btn-sm <?= $u['is_verified'] ? 'btn-success' : 'btn-delete' ?>"
                                            onclick="toggleVerify(<?= $u['id'] ?>, <?= $u['is_verified'] ? 0 : 1 ?>, this)"
                                            <?= $u['role'] === 'admin' ? 'disabled style="opacity:.5;"' : '' ?>>
                                        <?= $u['is_verified'] ? '&#10003; Verified' : '&#10005; Unverified' ?>
                                    </button>
                                </td>
                                <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                <td class="text-right">
                                    <?php if ($u['id'] !== $user['id'] && $u['role'] !== 'admin'): ?>
                                        <button class="btn-sm btn-delete" onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>')">Delete</button>
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

<!-- AJAX -->
<script>
(function () {
    var input   = document.getElementById('searchInput');
    var body    = document.getElementById('tableBody');
    var counter = document.getElementById('resultCount');
    var timer;

    function esc(s) {
        return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function roleBadge(r) {
        var c = r === 'admin' ? 'badge-danger' : (r === 'scout' ? 'badge-info' : 'badge-primary');
        return '<span class="badge ' + c + '">' + r.charAt(0).toUpperCase() + r.slice(1) + '</span>';
    }

    function render(rows) {
        if (!rows.length) { body.innerHTML = '<tr><td colspan="7" class="empty">No matching users.</td></tr>'; counter.textContent = '0 results'; return; }
        var html = '';
        rows.forEach(function (u, i) {
            var verBtn = u.role === 'admin' ? '<button class="btn-sm" disabled style="opacity:.5;">Admin</button>'
                : '<button class="btn-sm ' + (u.is_verified == 1 ? 'btn-success' : 'btn-delete') + '" onclick="toggleVerify(' + u.id + ',' + (u.is_verified == 1 ? 0 : 1) + ',this)">' + (u.is_verified == 1 ? '&#10003; Verified' : '&#10005; Unverified') + '</button>';
            var delBtn = u.role !== 'admin' ? '<button class="btn-sm btn-delete" onclick="deleteUser(' + u.id + ',\'' + esc(u.name) + '\')">Delete</button>' : '<span style="color:var(--text-light);font-size:12px;">--</span>';
            html += '<tr id="user-row-' + u.id + '"><td>' + (i+1) + '</td><td>' + esc(u.name) + '</td><td>' + esc(u.email) + '</td><td>' + roleBadge(u.role) + '</td><td>' + verBtn + '</td><td>' + esc(u.created_at) + '</td><td class="text-right">' + delBtn + '</td></tr>';
        });
        body.innerHTML = html;
        counter.textContent = rows.length + (input.value.trim() ? ' results' : ' total');
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('index.php?page=ajax&type=search_users&q=' + encodeURIComponent(input.value.trim()), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); }).then(render).catch(function (e) { console.error(e); });
        }, 200);
    });
})();

function toggleVerify(id, newVal, btn) {
    var formData = new FormData();
    formData.append('id', id);
    formData.append('is_verified', newVal);
    fetch('index.php?page=ajax&type=verify_toggle', { method: 'POST', body: formData, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.success) {
                btn.className = 'btn-sm ' + (newVal ? 'btn-success' : 'btn-delete');
                btn.innerHTML = newVal ? '&#10003; Verified' : '&#10005; Unverified';
                btn.setAttribute('onclick', 'toggleVerify(' + id + ',' + (newVal ? 0 : 1) + ',this)');
            }
        });
}

function deleteUser(id, name) {
    if (!confirm('Delete user "' + name + '"? This will remove all their data.')) return;
    var formData = new FormData();
    formData.append('id', id);
    fetch('index.php?page=ajax&type=delete_user', { method: 'POST', body: formData, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.success) { var row = document.getElementById('user-row-' + id); if (row) row.remove(); }
            else { alert(d.error || 'Failed to delete user.'); }
        });
}
</script>

</body>
</html>
