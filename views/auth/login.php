<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PHP-MINT</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<nav class="corner-nav-left">
    <a href="/" class="btn">BACK</a>
</nav>

<div class="container auth-container">
    <div class="description">
        LOGIN
    </div>

    <hr>

    <?php
    if (!empty($error)): ?>
        <div class="auth-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php
    endif; ?>

    <form method="POST" action="/login" class="auth-form">
        <div class="form-group">
            <label for="username">USERNAME</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">PASSWORD</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn">LOGIN</button>
    </form>

    <div class="auth-links">
        No account? <a href="/register">REGISTER</a>
    </div>

    <div class="footer">
        SYSTEM_STATUS: AUTHENTICATION_REQUIRED
    </div>
</div>

</body>
</html>
