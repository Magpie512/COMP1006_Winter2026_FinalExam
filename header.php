<?php if (!isset($pageTitle)) { $pageTitle = 'Image Gallery'; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body>
<nav>
    <?php if (!empty($_SESSION['admin_id'])): ?>
        Logged in as <?= htmlspecialchars($_SESSION['username']) ?> |
        <a href="gallery.php">Gallery</a> |
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="register.php">Register</a> |
        <a href="login.php">Login</a>
    <?php endif; ?>
</nav>
<hr>