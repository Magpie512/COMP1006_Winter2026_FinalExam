<?php
$pageTitle = 'Register';
require_once 'connect.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = :u OR email = :e');
        $stmt->execute([':u' => $username, ':e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admins (username, email, password_hash) VALUES (:u, :e, :p)');
            if ($stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash])) {
                $success = 'Registration successful. You can now log in.';
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<?php include 'header.php'; ?>

<h1>Register</h1>

<?php if ($success): ?>
    <p><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $err): ?>
    <p><?= htmlspecialchars($err) ?></p>
<?php endforeach; ?>

<form method="post" action="register.php" novalidate>
    <label>Username:
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </label><br>
    <label>Email:
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </label><br>
    <label>Password:
        <input type="password" name="password">
    </label><br>
    <label>Confirm Password:
        <input type="password" name="confirm_password">
    </label><br>
    <button type="submit">Register</button>
</form>