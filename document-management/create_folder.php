<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'conn.php';

$folderName = trim($_POST['folder_name'] ?? '');
$folderColor = trim($_POST['folder_color'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($folderName && $folderColor) {
        $stmt = $conn->prepare("INSERT INTO folders (name, folder_color, user_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ssi", $folderName, $folderColor, $_SESSION['user_id']);
        $stmt->execute();
        header("Location: view_files.php");
        exit;
    } else {
        $error = "Missing folder name or color.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Folder</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f2f4f8;
        }
        .create-folder-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .folder-box {
            background-color: #fff0f4;
            padding: 40px 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }
        h2 {
            text-align: center;
            font-weight: bold;
            color: #333;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-primary {
            background-color:rgb(239, 206, 213);
            border: none;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #c75b73;
        }
        .btn-secondary {
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container create-folder-wrapper">
    <div class="folder-box">
        <h2 class="mb-4">Create a New Folder</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="folder_name"><strong>Folder Name</strong></label>
                <input type="text" name="folder_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="folder_color"><strong>Folder Style</strong></label>
                <select name="folder_color" class="form-control" required>
                    <option value="">Select a color</option>
                    <option value="red">Light Pink</option>
                    <option value="yellow">Yellow</option>
                    <option value="blue">Light Blue</option>
                    <option value="grey">Grey</option>
                </select>
            </div>
            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-primary px-4">Create Folder</button>
                <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
