<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | PHP-MINT</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<nav class="corner-nav-left">
    <a href="/" class="btn">BACK</a>
</nav>

<div class="container auth-container">
    <div class="description">
        REGISTER
    </div>

    <hr>

    <?php
    if (!empty($error)): ?>
        <div class="auth-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php
    endif; ?>

    <form method="POST" action="/register" class="auth-form">
        <div class="form-group">
            <label for="username">USERNAME</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">PASSWORD</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">CONFIRM PASSWORD</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn">REGISTER</button>
    </form>

    <div class="auth-links">
        Have an account? <a href="/login">LOGIN</a>
    </div>

    <div class="footer">
        SYSTEM_STATUS: REGISTRATION_OPEN
    </div>
</div>

</body>
</html>
