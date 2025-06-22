<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'conn.php';
$user_id = $_SESSION['user_id'];


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $folder_id = $_POST['folder_id'];
    $new_name = trim($_POST['new_name']);
    $new_color = $_POST['new_color'];

    if (!empty($new_name) && !empty($new_color)) {
        $stmt = $conn->prepare("UPDATE folders SET name = ?, folder_color = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $new_name, $new_color, $folder_id, $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('Folder updated successfully!'); window.location.href = 'view_files.php';</script>";
        } else {
            echo "Error updating folder.";
        }
    } else {
        echo "Folder name and color cannot be empty.";
    }
} else {
    echo "Invalid request.";
}
?>
