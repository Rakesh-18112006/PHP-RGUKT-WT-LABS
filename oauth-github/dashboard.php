<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}
$u = $_SESSION['user'];
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container container">
        <div class="dashboard-header">
            <h2>Logged in with GitHub</h2>
        </div>
        <div class="profile-info">
            <?php if (!empty($u['avatar_url'])): ?>
                <img src="<?= htmlspecialchars($u['avatar_url']) ?>" width="80" alt="avatar" class="profile-pic">
            <?php endif; ?>
            <div>
                <p><b>Username:</b> <?= htmlspecialchars($u['login'] ?? '') ?></p>
                <p><b>Name:</b> <?= htmlspecialchars($u['name'] ?? '') ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($u['email'] ?? 'Not available') ?></p>
            </div>
        </div>
        <p><a href="logout.php" class="btn" style="margin-top: 2rem;">Logout</a></p>
    </div>
</body>
</html>
