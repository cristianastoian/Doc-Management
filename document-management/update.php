<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '_Nav.php';
include './conn.php';
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dczcid9av',
        'api_key'    => '548332352699147',
        'api_secret' => '0nLCgaYxvVXQ7ZGzFFbVcVfQJIY',
    ],
    'url' => ['secure' => true]
]);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "SELECT file_name, file_path, folder FROM uploads WHERE id = $id AND user_id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $oldFileName = $row['file_name'];
        $oldFilePath = $row['file_path'];
        $folder = $row['folder'] ?? '';
    } else {
        echo "<div class='alert alert-danger'>File not found or access denied.</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger'>No file specified.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newFileUploaded = isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK;
    $newFileNameText = trim($_POST['new_name'] ?? '');

    if ($newFileUploaded) {
        $newOriginalName = basename($_FILES['file']['name']);
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $extension = strtolower(pathinfo($newOriginalName, PATHINFO_EXTENSION));

        $cloudinaryFolder = 'doc_mgmt' . (!empty($folder) ? "/$folder" : '');
        $options = ['folder' => $cloudinaryFolder];
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $options['resource_type'] = 'raw';
        }

        try {
            $uploadResult = $cloudinary->uploadApi()->upload($fileTmpPath, $options);
            $newUrl = $uploadResult['secure_url'];

          
            $parsed = parse_url($oldFilePath, PHP_URL_PATH);
            $parts = explode('/', $parsed);
            $publicIdWithExt = end($parts);
            $publicId = pathinfo($publicIdWithExt, PATHINFO_FILENAME);
            $deletePath = $cloudinaryFolder . '/' . $publicId;
            $cloudinary->uploadApi()->destroy($deletePath);

           
            $finalName = $newFileNameText !== ''
                ? pathinfo($newFileNameText, PATHINFO_FILENAME) . '.' . $extension
                : $newOriginalName;

            $escapedName = $conn->real_escape_string($finalName);
            $escapedUrl = $conn->real_escape_string($newUrl);
            $conn->query("UPDATE uploads SET file_name = '$escapedName', file_path = '$escapedUrl' WHERE id = $id AND user_id = $user_id");

            echo "<script>alert('File updated successfully!'); window.location.href = 'view_files.php';</script>";
            exit;

        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Cloudinary Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }

    } elseif ($newFileNameText !== '') {
        $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
        $renamedWithExt = pathinfo($newFileNameText, PATHINFO_FILENAME) . '.' . $ext;
        $escapedRename = $conn->real_escape_string($renamedWithExt);
        $conn->query("UPDATE uploads SET file_name = '$escapedRename' WHERE id = $id AND user_id = $user_id");

        echo "<script>alert('File name updated.'); window.location.href = 'view_files.php';</script>";
        exit;

    } else {
        echo "<div class='alert alert-warning'>No changes were made.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update File</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Update File</h2>
    <p>Current file: <strong><?= htmlspecialchars($oldFileName); ?></strong></p>

    <form action="update.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="file">Select New File (optional)</label>
            <input type="file" name="file" id="file" class="form-control-file">
        </div>

        <div class="form-group mt-3">
            <label for="new_name">Change File Name (optional)</label>
            <input type="text" name="new_name" id="new_name" class="form-control" placeholder="e.g. updated-report">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update File</button>
    </form>

    <a href="view_files.php" class="btn btn-secondary mt-3">Back to Files</a>
</div>
</body>
</html>
