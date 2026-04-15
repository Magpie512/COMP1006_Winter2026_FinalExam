<?php
$pageTitle = 'Gallery';
require_once 'db.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';

// Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare('SELECT file_path FROM images WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $img = $stmt->fetch();

    if ($img) {
        $pdo->prepare('DELETE FROM images WHERE id = :id')->execute([':id' => $id]);
        $file = $img['file_path'];
        if ($file && file_exists($file)) {
            unlink($file);
        }
        $success = 'Image deleted successfully.';
    } else {
        $errors[] = 'Image not found.';
    }
}

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = trim($_POST['title'] ?? '');
    if ($title === '') {
        $errors[] = 'A title is required.';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed.';
    }

    $uploadPath = '';
    if (empty($errors)) {
        $fileTmp  = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileSize = $_FILES['image']['size'];

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
        }

        if ($fileSize > 2 * 1024 * 1024) {
            $errors[] = 'File is too large (MAX 2mb).';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $fileTmp);


        $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mime, $allowedMime, true)) {
            $errors[] = 'Uploaded file is not a valid image.';
        }

        if (empty($errors)) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            $newName = 'uploads/' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($fileTmp, $newName)) {
                $uploadPath = $newName;
            } else {
                $errors[] = 'Failed to move uploaded file.';
            }
        }
    }

    if (empty($errors) && $uploadPath !== '') {
        $stmt = $pdo->prepare('INSERT INTO images (admin_id, title, file_path) VALUES (:a, :t, :f)');
        if ($stmt->execute([
            ':a' => $_SESSION['admin_id'],
            ':t' => $title,
            ':f' => $uploadPath
        ])) {
            $success = 'Image uploaded successfully.';
        } else {
            $errors[] = 'Database error while saving image.';
        }
    }
}

// Fetch
$stmt = $pdo->query('SELECT images.id, images.title, images.file_path, admins.username 
                     FROM images 
                     JOIN admins ON images.admin_id = admins.id
                     ORDER BY images.created_at DESC');
$images = $stmt->fetchAll();
?>
<?php include 'partials/header.php'; ?>

<h1>Image Gallery</h1>

<?php if ($success): ?>
    <p><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $err): ?>
    <p><?= htmlspecialchars($err) ?></p>
<?php endforeach; ?>

<h2>Upload New Image</h2>
<form method="post" action="gallery.php" enctype="multipart/form-data" novalidate>
    <label>Title:
        <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
    </label><br>
    <label>Image:
        <input type="file" name="image" accept="image/*">
    </label><br>
    <button type="submit" name="upload" value="1">Upload</button>
</form>

<h2>All Images</h2>
<?php if (!$images): ?>
    <p>No images uploaded yet.</p>
<?php else: ?>
    <?php foreach ($images as $img): ?>
        <div>
            <p><strong><?= htmlspecialchars($img['title']) ?></strong></p>
            <p>Uploaded by <?= htmlspecialchars($img['username']) ?></p>
            <?php if (file_exists($img['file_path'])): ?>
                <img src="<?= htmlspecialchars($img['file_path']) ?>" alt="<?= htmlspecialchars($img['title']) ?>" style="max-width:200px;">
            <?php else: ?>
                <p>File missing on server.</p>
            <?php endif; ?>
            <p>
                <a href="gallery.php?delete=<?= (int)$img['id'] ?>" onclick="return confirm('Delete this image?');">
                    Delete
                </a>
            </p>
            <hr>
        </div>
    <?php endforeach; ?>
<?php endif; ?>