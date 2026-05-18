<?php $user = $_SESSION['user']; $isGeneralUser = ($user['role'] === 'user' && $user['is_verified'] == 1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($post['title']) ?> &mdash; Travel Guide</title>
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
            <a href="index.php?page=browse">Browse</a>
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
    <a href="index.php?page=browse" style="font-size:14px;color:var(--text-muted);display:inline-block;margin-bottom:16px;">&larr; Back to Browse</a>

    <!-- ============ Post Header ============ -->
    <div class="card" style="overflow:hidden;margin-bottom:24px;">
        <?php if (!empty($post['image'])): ?>
            <div style="height:300px;overflow:hidden;">
                <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
            </div>
        <?php else: ?>
            <div style="height:200px;background:linear-gradient(135deg,#99f6e4,#67e8f9);display:flex;align-items:center;justify-content:center;font-size:64px;color:rgba(255,255,255,.6);">&#127748;</div>
        <?php endif; ?>

        <div style="padding:28px;">
            <h1 style="font-size:28px;font-weight:700;margin-bottom:12px;"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-card-meta" style="margin-bottom:16px;">
                <span class="badge badge-info"><?= htmlspecialchars($post['country']) ?></span>
                <span class="badge badge-primary"><?= htmlspecialchars($post['genre']) ?></span>
                <?php $cc = $post['cost_level'] === 'low' ? 'badge-success' : ($post['cost_level'] === 'medium' ? 'badge-warning' : 'badge-danger'); ?>
                <span class="badge <?= $cc ?>"><?= ucfirst($post['cost_level']) ?> Cost</span>
            </div>
            <p style="color:var(--text-muted);font-size:13px;">Posted by <?= htmlspecialchars($post['scout_name']) ?> on <?= date('M d, Y', strtotime($post['created_at'])) ?></p>
        </div>
    </div>

    <!-- ============ Content ============ -->
    <div class="card form-card" style="margin-bottom:24px;">
        <h3 class="card-title">&#128214; About This Place</h3>
        <div style="line-height:1.8;color:var(--text);">
            <?= nl2br(htmlspecialchars($post['short_history'])) ?>
        </div>
    </div>

    <div class="card form-card" style="margin-bottom:24px;">
        <h3 class="card-title">&#9992; Travel Information</h3>
        <div style="line-height:1.8;color:var(--text);">
            <?= nl2br(htmlspecialchars($post['travel_medium_info'])) ?>
        </div>
    </div>

    <!-- ============ Cost Calculator ============ -->
    <div class="card form-card" style="margin-bottom:24px;">
        <h3 class="card-title">&#128176; Trip Cost Calculator</h3>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px;">
            Base cost per person per week: <strong>$<?= number_format($baseCost, 2) ?></strong>
            (<?= ucfirst($post['cost_level']) ?> level)
        </p>
        <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;">
            <div class="field" style="min-width:120px;">
                <label for="travelers">Travelers (1-10)</label>
                <input type="number" id="travelers" min="1" max="10" value="1">
                <span class="js-error" id="travelersError" style="color:#ef4444;font-size:12px;display:none;"></span>
            </div>
            <div class="field" style="min-width:120px;">
                <label for="days">Number of Days</label>
                <input type="number" id="days" min="1" max="90" value="7">
                <span class="js-error" id="daysError" style="color:#ef4444;font-size:12px;display:none;"></span>
            </div>
            <button class="btn btn-primary" onclick="calculateCost()" style="margin-bottom:6px;">Calculate</button>
        </div>
        <div id="costResult" style="margin-top:16px;display:none;">
            <div class="alert alert-success" style="font-size:16px;">
                Estimated Total: <strong id="costTotal">$0.00</strong>
                <br><small id="costBreakdown" style="font-size:12px;"></small>
            </div>
        </div>
    </div>

    <!-- ============ Comments ============ -->
    <div class="card" style="margin-bottom:24px;">
        <div class="card-toolbar">
            <h3 style="font-size:16px;font-weight:600;margin:0;">&#128172; Comments</h3>
            <span class="badge-count" id="commentCount"><?= count($comments) ?> comments</span>
        </div>

        <?php if ($isGeneralUser): ?>
        <div style="padding:20px;border-bottom:1px solid var(--border);">
            <form id="commentForm" style="display:flex;gap:12px;align-items:flex-start;">
                <input type="hidden" id="commentPostId" value="<?= $post['id'] ?>">
                <div style="flex:1;">
                    <textarea id="commentContent" rows="2" placeholder="Write a comment..." style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:14px;font-family:inherit;resize:vertical;"></textarea>
                    <span class="js-error" id="commentError" style="color:#ef4444;font-size:12px;display:none;"></span>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;"><span id="charCount">0</span>/1000 characters</div>
                </div>
                <button type="button" class="btn btn-primary" onclick="submitComment()" style="white-space:nowrap;">Post</button>
            </form>
        </div>
        <?php endif; ?>

        <div id="commentsList" style="padding:0;">
            <?php if (empty($comments)): ?>
                <div class="empty" style="padding:32px;" id="noComments">No comments yet. Be the first to share your thoughts!</div>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <div class="comment-item" id="comment-<?= $c['id'] ?>" style="padding:16px 20px;border-bottom:1px solid #f1f5f9;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <div>
                                <strong style="font-size:14px;"><?= htmlspecialchars($c['user_name']) ?></strong>
                                <span style="color:var(--text-muted);font-size:12px;margin-left:8px;"><?= date('M d, Y H:i', strtotime($c['created_at'])) ?></span>
                            </div>
                            <?php if ($c['user_id'] == $user['id']): ?>
                                <button class="btn-sm btn-delete" onclick="deleteComment(<?= $c['id'] ?>)">Delete</button>
                            <?php endif; ?>
                        </div>
                        <p style="font-size:14px;color:var(--text);line-height:1.6;"><?= htmlspecialchars($c['content']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

<!-- AJAX Scripts -->
<script>
var currentUserId = <?= intval($user['id']) ?>;
var currentUserName = <?= json_encode($user['name']) ?>;

// Character counter
var contentEl = document.getElementById('commentContent');
if (contentEl) {
    contentEl.addEventListener('input', function () {
        document.getElementById('charCount').textContent = this.value.length;
    });
}

// Cost Calculator
function calculateCost() {
    var travelers = parseInt(document.getElementById('travelers').value);
    var days = parseInt(document.getElementById('days').value);
    var tErr = document.getElementById('travelersError');
    var dErr = document.getElementById('daysError');
    var valid = true;

    tErr.style.display = 'none'; dErr.style.display = 'none';

    if (isNaN(travelers) || travelers < 1 || travelers > 10) {
        tErr.textContent = 'Enter 1-10 travelers.'; tErr.style.display = 'block'; valid = false;
    }
    if (isNaN(days) || days < 1) {
        dErr.textContent = 'Enter a positive number.'; dErr.style.display = 'block'; valid = false;
    }
    if (!valid) return;

    var postId = <?= intval($post['id']) ?>;
    fetch('index.php?page=ajax&type=calculate_cost&post_id=' + postId + '&travelers=' + travelers + '&days=' + days, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.success) {
                document.getElementById('costTotal').textContent = '$' + parseFloat(d.data.total).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
                document.getElementById('costBreakdown').textContent = '$' + d.data.base_cost + '/person/week x ' + d.data.travelers + ' travelers x ' + d.data.days + ' days / 7';
                document.getElementById('costResult').style.display = 'block';
            } else {
                alert(d.error || 'Calculation failed.');
            }
        });
}

// Comments
function submitComment() {
    var content = document.getElementById('commentContent').value.trim();
    var errEl = document.getElementById('commentError');

    if (content === '') { errEl.textContent = 'Comment cannot be empty.'; errEl.style.display = 'block'; return; }
    if (content.length > 1000) { errEl.textContent = 'Comment too long (max 1000 characters).'; errEl.style.display = 'block'; return; }
    errEl.style.display = 'none';

    var fd = new FormData();
    fd.append('post_id', document.getElementById('commentPostId').value);
    fd.append('content', content);

    fetch('index.php?page=ajax&type=add_comment', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.success) {
                var noComments = document.getElementById('noComments');
                if (noComments) noComments.remove();

                var c = d.comment;
                var html = '<div class="comment-item" id="comment-' + c.id + '" style="padding:16px 20px;border-bottom:1px solid #f1f5f9;animation:slideDown .3s ease;">' +
                    '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">' +
                        '<div><strong style="font-size:14px;">' + c.user_name + '</strong>' +
                        '<span style="color:var(--text-muted);font-size:12px;margin-left:8px;">Just now</span></div>' +
                        '<button class="btn-sm btn-delete" onclick="deleteComment(' + c.id + ')">Delete</button>' +
                    '</div>' +
                    '<p style="font-size:14px;color:var(--text);line-height:1.6;">' + c.content + '</p></div>';

                var list = document.getElementById('commentsList');
                list.insertAdjacentHTML('afterbegin', html);
                document.getElementById('commentContent').value = '';
                document.getElementById('charCount').textContent = '0';

                var cnt = document.querySelectorAll('.comment-item').length;
                document.getElementById('commentCount').textContent = cnt + ' comments';
            } else {
                alert(d.error || 'Failed to post comment.');
            }
        });
}

function deleteComment(id) {
    if (!confirm('Delete your comment?')) return;
    var fd = new FormData(); fd.append('id', id);
    fetch('index.php?page=ajax&type=delete_comment', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.success) {
                var el = document.getElementById('comment-' + id);
                if (el) el.remove();
                var cnt = document.querySelectorAll('.comment-item').length;
                document.getElementById('commentCount').textContent = cnt + ' comments';
            }
        });
}
</script>

</body>
</html>
