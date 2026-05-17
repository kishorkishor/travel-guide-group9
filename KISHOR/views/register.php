<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#127758;</div>

        <h1>Join Travel Guide</h1> 
        <p>Create your account to start exploring destinations and planning trips.</p>
        <ul class="feature-list">
            <li>&#10003; Browse and search travel posts</li>

            <li>&#10003; Save favorites to your wishlist</li>

            <li>&#10003; Comment and share experiences</li>
            <li>&#10003; Become a Scout to contribute</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="muted">Register as a user or scout</p>

            <?php if (!empty($error)): ?>

                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>

            <?php endif; ?>

            <form method="POST" action="index.php?page=register" class="form" novalidate id="registerForm">
                <div class="field">
                    <label for="name">Full Name</label>

                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($old['name']) ?>"
                           placeholder="e.g. John Doe" required>
                    <span class="js-error" id="nameError" style="color:#ef4444;font-size:12px;display:none;"></span>
                </div>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($old['email']) ?>"
                           placeholder="e.g. john@example.com" required>
                    <span class="js-error" id="emailError" style="color:#ef4444;font-size:12px;display:none;"></span>
                </div>

                <div class="field">
                    <label for="role">Register As</label>
                    <select id="role" name="role" required>
                        <option value="user" <?= ($old['role'] ?? '') === 'user' ? 'selected' : '' ?>>General User</option>
                        <option value="scout" <?= ($old['role'] ?? '') === 'scout' ? 'selected' : '' ?>>Scout (Contributor)</option>
                    </select>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"

                               placeholder="Min 8 characters" required>
                        <span class="js-error" id="passwordError" style="color:#ef4444;font-size:12px;display:none;"></span>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Repeat password" required>
                        <span class="js-error" id="confirmError" style="color:#ef4444;font-size:12px;display:none;"></span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-foot">Already registered?
                <a href="index.php?page=login">Sign in</a>
            </p>
        </div> 

    </div>

</div>

<script>
(function () {
    var form = document.getElementById('registerForm');

    function showErr(id, msg) {
        var el = document.getElementById(id);
        el.textContent = msg; el.style.display = msg ? 'block' : 'none';
    }

    var emailTimer;
    document.getElementById('email').addEventListener('input', function () {

        var email = this.value.trim();
        clearTimeout(emailTimer);
        if (email === '') { showErr('emailError', ''); return; }
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(email)) { showErr('emailError', 'Invalid email format.'); return; }

        emailTimer = setTimeout(function () {
            fetch('index.php?page=ajax&type=check_email&email=' + encodeURIComponent(email))
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    showErr('emailError', d.exists ? 'This email is already registered.' : '');
                });
        }, 300);
    });

    document.getElementById('password').addEventListener('input', function () {
        showErr('passwordError', this.value.length > 0 && this.value.length < 8 ? 'Must be at least 8 characters.' : '');

    });

    document.getElementById('confirm_password').addEventListener('input', function () {
        var pwd = document.getElementById('password').value;
        showErr('confirmError', this.value !== '' && this.value !== pwd ? 'Passwords do not match.' : '');
    });

    form.addEventListener('submit', function (e) { 

        var valid = true;
        var name = document.getElementById('name').value.trim();

        var email = document.getElementById('email').value.trim(); 

        var pwd = document.getElementById('password').value;
        var conf = document.getElementById('confirm_password').value;

        if (name === '') { showErr('nameError', 'Name is required.'); valid = false; }
        else { showErr('nameError', ''); }

        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(email)) { showErr('emailError', 'Valid email is required.'); valid = false; }

        if (pwd.length < 8) { showErr('passwordError', 'Must be at least 8 characters.'); valid = false; }

        if (pwd !== conf) { showErr('confirmError', 'Passwords do not match.'); valid = false; }

        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>

