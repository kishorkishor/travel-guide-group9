<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login &mdash; Travel Guide</title>
<link rel="stylesheet" href="../style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#127758;</div>
        <h1>Explore the World</h1>
        <p>Browse destinations, search with filters, read reviews, and plan your next trip.</p>
        <ul class="feature-list">
            <li>&#10003; Browse approved travel posts</li>
            <li>&#10003; Search by country, genre, cost</li>
            <li>&#10003; Post comments and reviews</li>
            <li>&#10003; Calculate trip costs</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Welcome</h2>
            <p class="muted">Sign in to explore destinations</p>

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

            <p class="hint">Sign in with any verified account to browse posts.</p>
        </div>
    </div>
</div>

</body>
</html>
