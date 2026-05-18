<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Request &mdash; Travel Guide</title>
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
            <h1 class="page-title">Edit Request #<?= intval($editing['id']) ?></h1>
            <p class="page-sub">Update your pending post request</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card form-card">
        <h3 class="card-title">&#9998; Edit Post Request</h3>
        <form method="POST" action="index.php?page=edit_request&id=<?= intval($editing['id']) ?>" class="form" enctype="multipart/form-data" novalidate>
            <div class="field">
                <label for="title">Destination Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="short_history">Short History / Description</label>
                <textarea id="short_history" name="short_history" rows="4" required><?= htmlspecialchars($editing['short_history'] ?? '') ?></textarea>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($editing['country'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="genre">Genre</label>
                    <select id="genre" name="genre" required>
                        <?php $genres = ['beach','mountain','city','historical','adventure','cultural','nature']; ?>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?= $g ?>" <?= ($editing['genre'] ?? '') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Cost Level</label>
                    <div style="display:flex;gap:16px;padding:10px 0;">
                        <?php foreach (['low','medium','high'] as $cl): ?>
                            <label class="checkbox">
                                <input type="radio" name="cost_level" value="<?= $cl ?>" <?= ($editing['cost_level'] ?? '') === $cl ? 'checked' : '' ?> required>
                                <?= ucfirst($cl) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="field">
                    <label for="image">Replace Image (optional)</label>
                    <?php if (!empty($editing['image'])): ?>
                        <div style="margin-bottom:8px;"><img src="<?= htmlspecialchars($editing['image']) ?>" alt="Current" style="max-height:80px;border-radius:8px;"></div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="field">
                <label for="travel_medium_info">Travel Medium Info</label>
                <textarea id="travel_medium_info" name="travel_medium_info" rows="3" required><?= htmlspecialchars($editing['travel_medium_info'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
                <a href="index.php?page=my_requests" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Request</button>
            </div>
        </form>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

</body>
</html>
