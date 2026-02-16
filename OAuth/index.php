<?php
$client = require __DIR__ . '/config.php';

$authUrl = $client->createAuthUrl();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Google OAuth PHP</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login">
        <h2>Login</h2>
        <a href="<?= htmlspecialchars($authUrl) ?>" class="btn">Login with Google</a>
    </div>
</body>
</html>
