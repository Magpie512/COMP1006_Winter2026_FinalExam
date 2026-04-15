<?php
$pageTitle = 'Login';
require_once 'connect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Both fields are required.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :u');
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>

<?php include 'header.php'; ?>

<h1>Login</h1>

<?php foreach ($errors as $err): ?>
    <p><?= htmlspecialchars($err) ?></p>
<?php endforeach; ?>

<form method="post" action="login.php" novalidate>
    <label>Username:
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </label><br>
    <label>Password:
        <input type="password" name="password">
    </label><br>
    <button type="submit">Login</button>
</form>