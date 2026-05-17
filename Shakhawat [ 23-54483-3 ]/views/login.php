<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#127758;</div>
        <h1>Admin Panel</h1>
        <p>Manage users, moderate posts, and oversee comments on the Travel Guide platform.</p>
        <ul class="feature-list">
            <li>&#10003; Manage and verify users</li>
            <li>&#10003; Approve or reject post requests</li>
            <li>&#10003; Moderate comments</li>
            <li>&#10003; Full platform oversight</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Admin Sign In</h2>
            <p class="muted">Sign in with your admin account</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login" class="form" novalidate>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($prefill ?? '') ?>"
                           placeholder="Enter admin email" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter password" required>
                </div>
                <label class="checkbox">
                    <input type="checkbox" name="remember" <?= !empty($prefill) ? 'checked' : '' ?>>
                    <span>Remember me for 30 days</span>
                </label>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <p class="hint"><strong>Default Admin:</strong> admin@travelguide.com / admin123</p>
        </div>
    </div>
</div>

</body>
</html>
