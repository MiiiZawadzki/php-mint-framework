<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PHP-MINT</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<nav class="corner-nav">
    <form method="POST" action="/logout">
        <button type="submit" class="btn btn-danger">LOGOUT</button>
    </form>
</nav>

<div class="container">
    <div class="description">
        DASHBOARD
    </div>

    <hr>

    <p style="font-size: 1.2rem;">
        Welcome, <span class="highlight"><?= htmlspecialchars($user->username) ?></span>.
    </p>

    <p style="font-size: 1rem; margin-top: 1rem;">
        USER_ID: <?= $user->id ?><br>
        SESSION: ACTIVE
    </p>

    <div class="footer">
        SYSTEM_STATUS: AUTHENTICATED<br>
        <a href="/" style="color: inherit;">HOME</a>
    </div>
</div>

</body>
</html>
