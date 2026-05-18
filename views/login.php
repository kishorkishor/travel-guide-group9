
<!-- updated login page -->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scout Login &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#127758;</div>
        <h1>Scout Portal</h1>
        <p>Submit travel destination information and manage your post requests for the Travel Guide.</p>
        <ul class="feature-list">
            <li>&#10003; Submit new destination posts</li>
            <li>&#10003; Track your request status</li>
            <li>&#10003; Edit and manage submissions</li>
            <li>&#10003; Request changes to published posts</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Scout Sign In</h2>
            <p class="muted">Sign in with your scout account</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login" class="form" novalidate>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($prefill ?? '') ?>"
                           placeholder="Enter your email" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                </div>
                <label class="checkbox">
                    <input type="checkbox" name="remember" <?= !empty($prefill) ? 'checked' : '' ?>>
                    <span>Remember me for 30 days</span>
                </label>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <p class="hint">Only verified scout accounts can access this portal.</p>
        </div>
    </div>
</div>

</body>
</html>
