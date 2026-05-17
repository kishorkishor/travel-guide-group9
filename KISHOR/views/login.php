<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#127758;</div>
        <h1>Travel Guide</h1>
        <p>Discover amazing places around the world. Sign in to explore destinations, plan trips, and save your favorites.</p>
        <ul class="feature-list">
            <li>&#10003; Explore destinations worldwide</li>
            <li>&#10003; Save places to your wishlist</li>
            <li>&#10003; Get cost estimates for trips</li>

            <li>&#10003; Secure session-based login</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Welcome Back</h2>

            <p class="muted">Please sign in to continue</p>

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

            <p class="auth-foot">Don't have an account?
                <a href="index.php?page=register">Create one here</a>
            </p> 
            <p class="hint"><strong>Default Admin:</strong> admin@travelguide.com / admin123</p>
        </div>
    </div>
</div>

</body>

</html>
