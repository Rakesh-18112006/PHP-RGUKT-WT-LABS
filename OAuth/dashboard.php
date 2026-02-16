<?php
session_start();

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit; 
}

$user = $_SESSION['user'];
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
            <h2>Welcome</h2>
        </div>
        <div class="profile-info">
            <img src="<?= htmlspecialchars($user['picture']) ?>" width="80" alt="profile" class="profile-pic">
            <div>
                <p><b>Name:</b> <?= htmlspecialchars($user['name']) ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
        <p><a href="logout.php" class="btn" style="margin-top: 2rem;">Logout</a></p>
    </div>
</body>
</html>
