<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

ini_set('display_errors', 1);
error_reporting(E_ALL);

include './conn.php';
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dczcid9av',
        'api_key'    => '548332352699147',
        'api_secret' => '0nLCgaYxvVXQ7ZGzFFbVcVfQJIY',
    ],
    'url' => ['secure' => true]
]);

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>No file specified.</div>";
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT file_name, file_path, folder FROM uploads WHERE id = $id AND user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>File not found or access denied.</div>";
    exit;
}

$row = $result->fetch_assoc();
$oldFileName = $row['file_name'];
$oldFilePath = $row['file_path'];
$folder = $row['folder'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newFileUploaded = isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK;
    $newFileNameText = trim($_POST['new_name'] ?? '');

    if ($newFileUploaded) {
        $newOriginalName = basename($_FILES['file']['name']);
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $extension = strtolower(pathinfo($newOriginalName, PATHINFO_EXTENSION));
        $publicId = pathinfo($newOriginalName, PATHINFO_FILENAME);

        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'xlsx', 'pptx', 'txt'];
        $finalExtension = in_array($extension, $validExtensions) ? $extension : 'tmp';

        $cloudinaryFolder = 'doc_mgmt' . (!empty($folder) ? "/$folder" : '');
        $options = [
            'folder' => $cloudinaryFolder,
            'use_filename' => true,
            'unique_filename' => false,
            'resource_type' => in_array($finalExtension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'raw',
            'format' => $finalExtension
        ];

        try {
            $uploadResult = $cloudinary->uploadApi()->upload($fileTmpPath, $options);
            $newUrl = $uploadResult['secure_url'];

            $parsed = parse_url($oldFilePath, PHP_URL_PATH);
            $parts = explode('/', $parsed);
            $publicIdWithExt = end($parts);
            $oldPublicId = pathinfo($publicIdWithExt, PATHINFO_FILENAME);
            $deletePath = $cloudinaryFolder . '/' . $oldPublicId;

            $resourceType = in_array($finalExtension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'raw';
            $cloudinary->uploadApi()->destroy($deletePath, ['resource_type' => $resourceType]);


            $finalName = $newFileNameText !== ''
                ? pathinfo($newFileNameText, PATHINFO_FILENAME) . '.' . $finalExtension
                : $newOriginalName;

            $escapedName = $conn->real_escape_string($finalName);
            $escapedUrl = $conn->real_escape_string($newUrl);

            $conn->query("UPDATE uploads SET file_name = '$escapedName', file_path = '$escapedUrl' WHERE id = $id AND user_id = $user_id");

            echo "<script>
                alert('File updated successfully!');
                window.location.href = 'view_folder.php?folder=" . urlencode($folder) . "';
            </script>";
            exit;

        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Cloudinary Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            exit;
        }

    } elseif ($newFileNameText !== '') {
        $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
        $renamedWithExt = pathinfo($newFileNameText, PATHINFO_FILENAME) . '.' . $ext;
        $escapedRename = $conn->real_escape_string($renamedWithExt);

        $conn->query("UPDATE uploads SET file_name = '$escapedRename' WHERE id = $id AND user_id = $user_id");

        echo "<script>
            alert('File name updated.');
            window.location.href = 'view_folder.php?folder=" . urlencode($folder) . "';
        </script>";
        exit;

    } else {
        echo "<div class='alert alert-warning'>No changes were made.</div>";
        exit;
    }
}

include '_Nav.php';
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
            <input type="file" name="file" id="file" class="form-control-file"
                   accept=".jpg,.jpeg,.png,.gif,.pdf,.docx,.xlsx,.pptx,.txt">
        </div>

        <div class="form-group mt-3">
            <label for="new_name">Change File Name (optional)</label>
            <input type="text" name="new_name" id="new_name" class="form-control" placeholder="e.g. updated-report">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update File</button>
    </form>

    <a href="view_folder.php?folder=<?= urlencode($folder) ?>" class="btn btn-secondary mt-3">Back to Folder</a>
</div>
</body>
</html>
