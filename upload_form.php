<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '_Nav.php';
include 'conn.php';
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File</title>
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
            background-color:rgb(244, 193, 204);
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

<div class="container upload-wrapper">
    <div class="upload-box">
        <h2 class="mb-4">Upload a File</h2>
        <form action="upload.php" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="file"><strong>Choose File</strong></label>
                <input type="file" name="file" id="file" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="folder"><strong>Folder Name</strong> <small class="text-muted">(optional)</small></label>
                <input type="text" name="folder" id="folder" list="folderList" class="form-control" placeholder="Type or choose a folder">
                <datalist id="folderList">
                    <?php
                    $folderNames = [];

                    $stmt = $conn->prepare("SELECT name FROM folders WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($folder = $result->fetch_assoc()) {
                        $folderNames[] = $folder['name'];
                        echo "<option value='" . htmlspecialchars($folder['name']) . "'>";
                    }
                    ?>
                </datalist>
            </div>

            <div class="form-group">
                <label for="folder_icon"><strong>Folder Style</strong> <small class="text-muted">(choose color)</small></label>
                <select name="folder_icon" id="folder_icon" class="form-control">
                    <option value="">Select a color</option>
                    <option value="red">Light Pink</option>
                    <option value="yellow">Yellow</option>
                    <option value="blue">Light Blue</option>
                    <option value="grey">Grey</option>
                </select>
            </div>

            <div class="form-group text-center mt-4">
                <button type="submit" name="upload" class="btn btn-primary px-4">Upload</button>
                <a href="view_files.php" class="btn btn-secondary ml-2">Back</a>
            </div>
        </form>
    </div>
</div>

<script>
const existingFolders = <?= json_encode($folderNames) ?>;
const folderInput = document.getElementById("folder");
const folderStyleSelect = document.getElementById("folder_icon");

folderInput.addEventListener("input", function () {
    const typed = folderInput.value.trim();
    if (typed !== "" && existingFolders.includes(typed)) {
        folderStyleSelect.disabled = true;
        folderStyleSelect.title = "This folder already exists and has a style.";
        folderStyleSelect.classList.add("bg-light");
        folderStyleSelect.style.cursor = "not-allowed";
    } else {
        folderStyleSelect.disabled = false;
        folderStyleSelect.title = "Choose a color for the new folder.";
        folderStyleSelect.classList.remove("bg-light");
        folderStyleSelect.style.cursor = "pointer";
    }
});

document.querySelector("form").addEventListener("submit", function (e) {
    const folderName = folderInput.value.trim();
    const folderStyle = folderStyleSelect.value.trim();
    if (folderName !== "" && !existingFolders.includes(folderName) && folderStyle === "") {
        e.preventDefault();
        alert("Please select a folder style color if you're creating a new folder.");
        folderStyleSelect.focus();
    }
});
</script>

</body>
</html>
