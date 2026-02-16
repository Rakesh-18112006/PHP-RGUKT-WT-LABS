<?php
require __DIR__ . '/config.php';

// CSRF protection (state)
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
  'client_id' => GITHUB_CLIENT_ID,
  'redirect_uri' => GITHUB_REDIRECT_URI,
  'scope' => 'read:user user:email',
  'state' => $state,
]);

$authUrl = GITHUB_AUTHORIZE_URL . '?' . $params;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>GitHub OAuth</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login">
        <h2>Login</h2>
        <a href="<?= htmlspecialchars($authUrl) ?>" class="btn">Login with GitHub</a>
    </div>
</body>
</html>



