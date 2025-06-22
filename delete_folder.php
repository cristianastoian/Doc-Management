<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'conn.php';
$user_id = $_SESSION['user_id'];


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['folder_id'])) {
    $folder_id = $_POST['folder_id'];

  
    $stmt = $conn->prepare("SELECT name FROM folders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folder_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $folder = $result->fetch_assoc();

    if ($folder) {
        $folder_name = $folder['name'];

        
        $delUploads = $conn->prepare("DELETE FROM uploads WHERE folder = ? AND user_id = ?");
        $delUploads->bind_param("si", $folder_name, $user_id);
        $delUploads->execute();

       
        $delete = $conn->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
        $delete->bind_param("ii", $folder_id, $user_id);
        $delete->execute();

        echo "<script>alert('Folder and its files deleted.'); window.location.href = 'view_files.php';</script>";
    } else {
        echo "Folder not found.";
    }
} else {
    echo "Invalid request.";
}
?>
