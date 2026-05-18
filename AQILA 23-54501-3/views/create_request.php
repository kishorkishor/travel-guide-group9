<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Request &mdash; Travel Guide</title>
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
            <a href="index.php?page=create_request" class="active">New Request</a>
            <a href="index.php?page=my_requests">My Requests</a>
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
            <h1 class="page-title">Submit New Destination</h1>
            <p class="page-sub">Fill in the details about a visiting place for admin review</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card form-card">
        <h3 class="card-title">+ New Post Request</h3>
        <form method="POST" action="index.php?page=create_request" class="form" enctype="multipart/form-data" novalidate id="requestForm">
            <div class="field">
                <label for="title">Destination Title</label>
                <input type="text" id="title" name="title" placeholder="e.g. Santorini, Greece" required>
                <span class="js-error" id="titleError" style="color:#ef4444;font-size:12px;display:none;"></span>
            </div>
            <div class="field">
                <label for="short_history">Short History / Description</label>
                <textarea id="short_history" name="short_history" rows="4" placeholder="Brief history and cultural significance of this place..." required></textarea>
                <span class="js-error" id="historyError" style="color:#ef4444;font-size:12px;display:none;"></span>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" placeholder="e.g. Greece" required>
                    <span class="js-error" id="countryError" style="color:#ef4444;font-size:12px;display:none;"></span>
                </div>
                <div class="field">
                    <label for="genre">Genre</label>
                    <select id="genre" name="genre" required>
                        <option value="">Select genre...</option>
                        <option value="beach">Beach</option>
                        <option value="mountain">Mountain</option>
                        <option value="city">City</option>
                        <option value="historical">Historical</option>
                        <option value="adventure">Adventure</option>
                        <option value="cultural">Cultural</option>
                        <option value="nature">Nature</option>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Cost Level</label>
                    <div style="display:flex;gap:16px;padding:10px 0;">
                        <label class="checkbox"><input type="radio" name="cost_level" value="low" required> Low</label>
                        <label class="checkbox"><input type="radio" name="cost_level" value="medium"> Medium</label>
                        <label class="checkbox"><input type="radio" name="cost_level" value="high"> High</label>
                    </div>
                </div>
                <div class="field">
                    <label for="image">Destination Image (optional)</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    <span style="font-size:11px;color:var(--text-muted);">JPG, PNG, WebP - max 5MB</span>
                </div>
            </div>
            <div class="field">
                <label for="travel_medium_info">Travel Medium Info</label>
                <textarea id="travel_medium_info" name="travel_medium_info" rows="3" placeholder="e.g. Flight from major cities, local bus service, ferry available..." required></textarea>
            </div>
            <div class="form-actions">
                <a href="index.php?page=my_requests" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit Request</button>
            </div>
        </form>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

<!-- JS Validation -->
<script>
(function () {
    var form = document.getElementById('requestForm');
    function showErr(id, msg) {
        var el = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
    }

    form.addEventListener('submit', function (e) {
        var valid = true;
        if (document.getElementById('title').value.trim() === '') { showErr('titleError', 'Title is required.'); valid = false; }
        else { showErr('titleError', ''); }

        if (document.getElementById('short_history').value.trim() === '') { showErr('historyError', 'Description is required.'); valid = false; }
        else { showErr('historyError', ''); }

        if (document.getElementById('country').value.trim() === '') { showErr('countryError', 'Country is required.'); valid = false; }
        else { showErr('countryError', ''); }

        if (document.getElementById('genre').value === '') { valid = false; }

        var costChecked = document.querySelector('input[name="cost_level"]:checked');
        if (!costChecked) { valid = false; }

        // File type check
        var fileInput = document.getElementById('image');
        if (fileInput.files.length > 0) {
            var allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (allowed.indexOf(fileInput.files[0].type) === -1) {
                alert('Image must be JPG, PNG, or WebP.');
                valid = false;
            }
            if (fileInput.files[0].size > 5 * 1024 * 1024) {
                alert('Image must be under 5MB.');
                valid = false;
            }
        }

        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>
