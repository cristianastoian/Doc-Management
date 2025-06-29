<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'conn.php';
$user_id = $_SESSION['user_id'];

$folderId = $_GET['folder_id'] ?? null;
$folderName = '';
$folderColor = '';

if ($folderId) {
    $stmt = $conn->prepare("SELECT name, folder_color FROM folders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folderId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $folderName = $row['name'];
        $folderColor = $row['folder_color'];
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'update') {
    $new_name = trim($_POST['new_name']);
    $new_color = $_POST['new_color'];

    if (!empty($new_name) && !empty($new_color)) {
        $stmt = $conn->prepare("UPDATE folders SET name = ?, folder_color = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $new_name, $new_color, $folderId, $user_id);
        if ($stmt->execute()) {
            
            $updateUploads = $conn->prepare("UPDATE uploads SET folder = ? WHERE folder = ? AND user_id = ?");
            $updateUploads->bind_param("ssi", $new_name, $folderName, $user_id);
            $updateUploads->execute();

            echo "<script>alert('Folder updated successfully!'); window.location.href = 'view_files.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating folder.');</script>";
        }
    } else {
        echo "<script>alert('Folder name and color cannot be empty.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Folder</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f4f8;
        }
        .upload-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .upload-box {
            width: 100%;
            max-width: 500px;
            background-color: #fff0f4;
            padding: 40px 30px;
            border-radius: 12px;
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
            background-color: rgb(244, 193, 204);
            border: none;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: rgb(221, 157, 231);
        }
        .btn-danger {
            background-color: #e74c3c;
            border: none;
            border-radius: 8px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="upload-wrapper">
    <div class="upload-box">
        <h2>Edit Folder</h2>

        <form method="POST">
            <input type="hidden" name="action" value="update">
            <div class="form-group">
                <label for="new_name"><strong>Folder Name</strong></label>
                <input type="text" class="form-control" id="new_name" name="new_name"
                       value="<?= htmlspecialchars($folderName) ?>" required>
            </div>

            <div class="form-group">
                <label for="new_color"><strong>Folder Style</strong> <small>(choose color)</small></label>
                <select class="form-control" id="new_color" name="new_color" required>
                    <option value="red" <?= $folderColor === 'red' ? 'selected' : '' ?>>Light Pink</option>
                    <option value="yellow" <?= $folderColor === 'yellow' ? 'selected' : '' ?>>Yellow</option>
                    <option value="blue" <?= $folderColor === 'blue' ? 'selected' : '' ?>>Light Blue</option>
                    <option value="grey" <?= $folderColor === 'grey' ? 'selected' : '' ?>>Grey</option>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary w-100">Update</button>
            </div>
        </form>

        <form method="GET" action="delete.php" onsubmit="return confirm('Are you sure you want to delete this folder and all its files?');">
            <input type="hidden" name="folder" value="<?= htmlspecialchars($folderName) ?>">
            <button type="submit" class="btn btn-danger w-100 mt-2">Delete</button>
        </form>

        <form method="GET" action="view_files.php">
            <button type="submit" class="btn btn-secondary w-100 mt-2">Back</button>
        </form>
    </div>
</div>

</body>
</html>
