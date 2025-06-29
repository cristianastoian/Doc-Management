<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include './conn.php';
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dczcid9av',
        'api_key'    => '548332352699147',
        'api_secret' => '0nLCgaYxvVXQ7ZGzFFbVcVfQJIY'
    ],
    'url' => ['secure' => true]
]);

$user_id = $_SESSION['user_id'];

function getResourceType($ext) {
    $ext = strtolower($ext);
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'jfif', 'webp'];
    return in_array($ext, $imageTypes) ? 'image' : 'raw';
}

if (isset($_GET['folder']) && $_GET['folder'] !== '') {
    $folderName = $_GET['folder'];

   
    $stmt = $conn->prepare("SELECT file_path, file_name FROM uploads WHERE folder = ? AND user_id = ?");
    $stmt->bind_param("si", $folderName, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $fileUrl = $row['file_path'];
        $originalName = $row['file_name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $publicId = pathinfo($originalName, PATHINFO_FILENAME);

        try {
            $cloudinary->uploadApi()->destroy("doc_mgmt/$folderName/$publicId", [
                'resource_type' => getResourceType($ext)
            ]);
        } catch (Exception $e) {
          
        }
    }

   
    $conn->query("DELETE FROM uploads WHERE folder = '$folderName' AND user_id = $user_id");

    $deleteFolder = $conn->prepare("DELETE FROM folders WHERE name = ? AND user_id = ?");
    $deleteFolder->bind_param("si", $folderName, $user_id);
    $deleteFolder->execute();

    echo "<script>alert('Folder and its files deleted successfully!'); window.location.href = 'view_files.php';</script>";
    exit;
}

if (isset($_GET['id'])) {
    $fileId = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT file_path, file_name, folder FROM uploads WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $fileId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file) {
        $fileUrl = $file['file_path'];
        $originalName = $file['file_name'];
        $folderName = $file['folder'] ?: 'Uncategorized';
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $publicId = pathinfo($originalName, PATHINFO_FILENAME);

        try {
            $cloudinary->uploadApi()->destroy("doc_mgmt/$folderName/$publicId", [
                'resource_type' => getResourceType($ext)
            ]);

            $deleteStmt = $conn->prepare("DELETE FROM uploads WHERE id = ? AND user_id = ?");
            $deleteStmt->bind_param("ii", $fileId, $user_id);
            $deleteStmt->execute();

            echo "<script>alert('File deleted successfully!'); window.location.href = 'view_folder.php?folder=" . urlencode($folderName) . "';</script>";
            exit;

        } catch (Exception $e) {
            echo "Cloud delete error: " . $e->getMessage();
        }
    } else {
        echo "File not found.";
    }

    $conn->close();
} else {
    echo "No file or folder specified.";
}
?>
