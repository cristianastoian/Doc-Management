<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'conn.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'update') {
    $folder_id = $_POST['folder_id'];
    $new_name = trim($_POST['new_name']);
    $new_color = $_POST['new_color'];

    if (!empty($new_name) && !empty($new_color)) {
        $stmt = $conn->prepare("UPDATE folders SET name = ?, folder_color = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $new_name, $new_color, $folder_id, $user_id);
        if ($stmt->execute()) {
            echo "<script>alert('Folder updated successfully!'); window.location.href = 'view_files.php';</script>";
        } else {
            echo "<script>alert('Error updating folder.');</script>";
        }
    } else {
        echo "<script>alert('Folder name and color cannot be empty.');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $folder_id = $_POST['folder_id'];
    $stmt = $conn->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folder_id, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('Folder deleted successfully.'); window.location.href = 'view_files.php';</script>";
    } else {
        echo "<script>alert('Error deleting folder.');</script>";
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
            margin-right: 190px;
            
        }

        .btn-primary:hover {
            background-color: rgb(221, 157, 231);
        }

        .btn-danger {
            background-color: #e74c3c;
            border: none;
            border-radius: 8px;
             margin-right: 10px;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
             margin-right: 10px;
           
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-block {
            width: 100%;
        }

        .button-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="upload-wrapper">
    <div class="upload-box">
        <h2>Edit Folder</h2>
        <form method="POST">
            <input type="hidden" name="folder_id" value="<?= htmlspecialchars($_GET['id'] ?? $_POST['folder_id'] ?? '') ?>">

            <div class="form-group">
                <label for="new_name"><strong>Folder Name</strong></label>
                <input type="text" class="form-control" id="new_name" name="new_name" required>
            </div>

            <div class="form-group">
                <label for="new_color"><strong>Folder Style</strong> <small>(choose color)</small></label>
                <select class="form-control" id="new_color" name="new_color" required>
                    <option value="red">Light Pink</option>
                    <option value="yellow">Yellow</option>
                    <option value="blue">Light Blue</option>
                    <option value="grey">Grey</option>
                </select>
            </div>
<div class="d-flex justify-content-between gap-2 mt-3">
    <form method="POST" style="flex: 1; margin-right: 5px;">
        <input type="hidden" name="folder_id" value="<?= htmlspecialchars($_GET['id'] ?? $_POST['folder_id'] ?? '') ?>">
        <input type="hidden" name="action" value="update">
        <button type="submit" class="btn btn-primary w-100">Update</button>
    </form>

    <form method="POST" style="flex: 1; margin-right: 5px;">
        <input type="hidden" name="folder_id" value="<?= htmlspecialchars($_GET['id'] ?? $_POST['folder_id'] ?? '') ?>">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn btn-danger w-100"
            onclick="return confirm('Are you sure you want to delete this folder and all its files?');">
            Delete
        </button>
    </form>

    <form method="GET" action="view_files.php" style="flex: 1;">
        <button type="submit" class="btn btn-secondary w-100">Back</button>
    </form>
</div>

</div>

</body>
</html>