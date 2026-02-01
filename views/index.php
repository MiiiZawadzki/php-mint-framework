<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP-mini | Framework</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <h1>PHP-MINT</h1>

    <div class="description">
        Build it <span id="dynamic-text">Fast</span>.
    </div>

    <hr>

    <p style="font-size: 1.2rem; max-width: 600px;">
        A lightweight, brutalist PHP micro-framework designed for developers who demand total control and zero
        bloatware.
    </p>

    <div class="footer">
        SYSTEM_STATUS: READY<br>
        <?php
        if (isset($version)) {
            echo 'CORE_VERSION: '.htmlspecialchars($version).'<br>';
        }
        ?>
        <?php
        echo date('Y');
        ?>
    </div>
</div>

<script src="assets/script.js"></script>
</body>
</html>