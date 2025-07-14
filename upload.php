<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

include '_Nav.php';
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

if (isset($_POST['upload']) && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = basename($_FILES['file']['name']);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $publicId = pathinfo($fileName, PATHINFO_FILENAME);

    $folderInput = trim($_POST['folder'] ?? '');
    if ($folderInput === '') $folderInput = 'Uncategorized';

    $folderStyleInput = trim($_POST['folder_icon'] ?? '');
    if ($folderStyleInput === '') $folderStyleInput = 'red';

    $folderEscaped = $conn->real_escape_string($folderInput);
    $folderStyleEscaped = $conn->real_escape_string($folderStyleInput);
    $fileNameEscaped = $conn->real_escape_string($fileName);

    try {
        $cloudinaryFolder = "doc_mgmt/$folderEscaped";

        $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $docTypes = ['pdf', 'docx', 'xlsx', 'pptx', 'txt'];
        $validExtensions = array_merge($imageTypes, $docTypes);

        $resourceType = in_array($extension, $imageTypes) ? 'image' : 'raw';

        if (!in_array($extension, $validExtensions)) {
            throw new Exception("Unsupported file type: .$extension");
        }

        $options = [
            'folder' => $cloudinaryFolder,
            'public_id' => $publicId,
            'use_filename' => true,
            'unique_filename' => false,
            'resource_type' => $resourceType,
            'format' => $extension
        ];

        $uploadResult = $cloudinary->uploadApi()->upload($fileTmpPath, $options);
        $publicUrl = $conn->real_escape_string($uploadResult['secure_url']);

        $sql = "INSERT INTO uploads (file_name, file_path, user_id, folder) 
                VALUES ('$fileNameEscaped', '$publicUrl', $user_id, '$folderEscaped')";
        $conn->query($sql);


        $check = $conn->prepare("SELECT id FROM folders WHERE user_id = ? AND name = ?");
        $check->bind_param("is", $user_id, $folderEscaped);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO folders (name, folder_color, user_id) VALUES (?, ?, ?)");
            $insert->bind_param("ssi", $folderEscaped, $folderStyleEscaped, $user_id);
            $insert->execute();
        }

        echo "<script>
            alert('File uploaded successfully to the cloud!');
            window.location.href = 'view_folder.php?folder=" . urlencode($folderInput) . "';
        </script>";
        exit;

    } catch (Exception $e) {
        echo "<div style='color:red;'>Cloudinary Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

} elseif (isset($_POST['upload'])) {
    echo "<div style='color:red;'>Upload error: No file uploaded or upload failed.</div>";
}
?>