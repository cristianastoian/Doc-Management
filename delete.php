<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include './conn.php';
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dczcid9av',
        'api_key'    => '548332352699147',
        'api_secret' => '0nLCgaYxvVXQ7ZGzFFbVcVfQJIY'
    ],
    'url' => ['secure' => true]
]);

if (isset($_GET['id'])) {
    $fileId = (int)$_GET['id'];

    // Get file path and folder before deleting
    $stmt = $conn->prepare("SELECT file_path, folder FROM uploads WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file) {
        $fileUrl = $file['file_path'];
        $folderName = $file['folder'] ?: 'Uncategorized';

        // Get public ID from Cloudinary URL
        $parts = explode('/', parse_url($fileUrl, PHP_URL_PATH));
        $publicIdWithExt = end($parts);
        $publicId = pathinfo($publicIdWithExt, PATHINFO_FILENAME);

        try {
            // Attempt to delete from Cloudinary
            $cloudinary->uploadApi()->destroy("doc_mgmt/$folderName/$publicId", [
                'resource_type' => 'raw'
            ]);

            // Delete from database
            $deleteStmt = $conn->prepare("DELETE FROM uploads WHERE id = ?");
            $deleteStmt->bind_param("i", $fileId);
            $deleteStmt->execute();

            echo "<script>alert('File deleted successfully!');</script>";
            header("Location: view_folder.php?folder=" . urlencode($folderName));
            exit;
        } catch (Exception $e) {
            echo "Cloud delete error: " . $e->getMessage();
        }
    } else {
        echo "File not found.";
    }

    $conn->close();
} else {
    echo "No file ID specified.";
}
?>
