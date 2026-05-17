<?php $u = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Profile &mdash; Travel Guide</title>
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

            <a href="index.php?page=profile" class="active">Profile</a>
            <?php if ($u['role'] === 'user' && $u['is_verified'] == 1): ?>
                <a href="index.php?page=wishlist">Wishlist</a>
            <?php endif; ?>
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
            <h1 class="page-title">My Profile</h1>
            <p class="page-sub">Update your personal information and password</p>
        </div>
        <span class="badge <?= $user['is_verified'] ? 'badge-success' : 'badge-warning' ?>">
            <?= $user['is_verified'] ? '&#10003; Verified' : '&#9203; Pending Verification' ?>
        </span>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    
    <div class="card form-card">
        <h3 class="card-title">&#128100; Personal Information</h3>

        <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;">
            <div class="profile-avatar-large">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Avatar">
                <?php else: ?>

                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-weight:600;font-size:18px;"><?= htmlspecialchars($user['name']) ?></div>
                <div style="color:var(--text-muted);font-size:14px;"><?= htmlspecialchars($user['email']) ?></div>
                <div style="color:var(--text-muted);font-size:12px;margin-top:4px;">Member since <?= date('M Y', strtotime($user['created_at'])) ?></div>

            </div>
        </div> 

        <form method="POST" action="index.php?page=profile&action=update" class="form" enctype="multipart/form-data" novalidate id="profileForm">
            <div class="field-row">
                <div class="field">

                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($user['name']) ?>" required>
                    <span class="js-error" id="pNameError" style="color:#ef4444;font-size:12px;display:none;"></span>

                </div>
                <div class="field">

                    <label for="email">Email Address</label>
                    <input type="email" id="pemail" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                    <span class="js-error" id="pEmailError" style="color:#ef4444;font-size:12px;display:none;"></span>
                </div>
            </div>
            <div class="field">
                <label for="profile_picture">Profile Picture (JPG, PNG, GIF, WebP - max 2MB)</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            </div>
            <div class="form-actions">

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>

        </form>
    </div>

    
    <div class="card form-card"> 
        <h3 class="card-title">&#128274; Change Password</h3>
        <form method="POST" action="index.php?page=profile&action=change_password" class="form" novalidate id="passwordForm">
            <div class="field">
                <label for="current_password">Current Password</label>

                <input type="password" id="current_password" name="current_password"
                       placeholder="Enter your current password" required> 
            </div>
            <div class="field-row">

                <div class="field">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password"
                           placeholder="Min 8 characters" required>
                    <span class="js-error" id="newPwdError" style="color:#ef4444;font-size:12px;display:none;"></span>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat new password" required>
                    <span class="js-error" id="confPwdError" style="color:#ef4444;font-size:12px;display:none;"></span>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Change Password</button>
            </div>
        </form>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide System</footer>

<script>
(function () {
    function showErr(id, msg) {
        var el = document.getElementById(id);
        el.textContent = msg; el.style.display = msg ? 'block' : 'none';

    }

    document.getElementById('profileForm').addEventListener('submit', function (e) {
        var valid = true;
        var name = document.getElementById('name').value.trim();
        var email = document.getElementById('pemail').value.trim();
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (name === '') { showErr('pNameError', 'Name is required.'); valid = false; }
        else { showErr('pNameError', ''); }

        if (!re.test(email)) { showErr('pEmailError', 'Valid email is required.'); valid = false; }
        else { showErr('pEmailError', ''); }

        if (!valid) e.preventDefault();
    });

    document.getElementById('new_password').addEventListener('input', function () {

        showErr('newPwdError', this.value.length > 0 && this.value.length < 8 ? 'Must be at least 8 characters.' : '');
    });

    document.getElementById('confirm_password').addEventListener('input', function () {
        var pwd = document.getElementById('new_password').value;

        showErr('confPwdError', this.value !== '' && this.value !== pwd ? 'Passwords do not match.' : '');
    });

    document.getElementById('passwordForm').addEventListener('submit', function (e) {
        var valid = true;
        var newPwd = document.getElementById('new_password').value; 
        var confPwd = document.getElementById('confirm_password').value;

        if (newPwd.length < 8) { showErr('newPwdError', 'Must be at least 8 characters.'); valid = false; }
        if (newPwd !== confPwd) { showErr('confPwdError', 'Passwords do not match.'); valid = false; }

        if (!valid) e.preventDefault();
    });
})();
</script>

</body> 
</html> 
