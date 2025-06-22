<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
include '_Nav.php';
include './conn.php';

$folderName = $_GET['folder'] ?? 'Uncategorized';
$view = $_GET['view'] ?? 'grid';
$sort = $_GET['sort'] ?? 'uploaded_at_desc';

switch ($sort) {
    case 'name_asc': $orderBy = "file_name ASC"; break;
    case 'name_desc': $orderBy = "file_name DESC"; break;
    case 'uploaded_at_asc': $orderBy = "uploaded_at ASC"; break;
    default: $orderBy = "uploaded_at DESC";
}

$stmt = $conn->prepare("SELECT id, file_name, file_path, uploaded_at FROM uploads WHERE user_id = ? AND folder = ? ORDER BY $orderBy");
$stmt->bind_param("is", $user_id, $folderName);
$stmt->execute();
$result = $stmt->get_result();
$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($folderName) ?> - Files</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
    body { background-color: #f8f9fa; }

    .file-card, .file-row {
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        margin: 10px;
    }

    .file-card {
        width: 220px;
        height: 250px;
        display: inline-block;
        vertical-align: top;
        overflow: hidden;
        position: relative;
    }

    .file-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
    }

    .preview-img, .doc-preview {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
    }

    .three-dots {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #eee;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        font-size: 18px;
        cursor: pointer;
        z-index: 1;
    }

    .dropdown-actions {
        display: none;
        position: absolute;
        top: 45px;
        right: 5px;
        background: white;
        border: 1px solid #ccc;
        border-radius: 5px;
        z-index: 100;
        width: 120px;
    }

    .dropdown-actions a {
        display: block;
        padding: 8px;
        color: #000;
        text-decoration: none;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }

    .dropdown-actions a:last-child {
        border-bottom: none;
    }

    .dropdown-actions a:hover {
        background-color: #f8f9fa;
    }

    .file-meta {
        padding: 10px;
        text-align: center;
        font-size: 14px;
    }

    .file-row .file-info {
        flex-grow: 1;
        margin-left: 20px;
    }

    .file-row img {
        width: 100px;
        height: 50px;
        object-fit:scale-down;
    }

    .add-file-card {
        width: 100px;
        height: 100px;
        display: inline-block;
        margin: 50px 10px 10px 10px;
        border-radius: 50%;
        border: 2px dashed #ccc;
        color: #6c757d;
        font-size: 2rem;
        font-weight: bold;
        text-align: center;
        line-height: 90px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .add-file-card:hover { background-color: #f1f1f1; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Files in "<?= htmlspecialchars($folderName) ?>"</h2>
    <a href="view_files.php" class="btn btn-secondary mb-3">&larr; Back to Folders</a>

    <form method="GET" class="form-inline mb-3">
        <input type="hidden" name="folder" value="<?= htmlspecialchars($folderName) ?>">
        <label class="mr-2">Sort by:</label>
        <select name="sort" onchange="this.form.submit()" class="form-control mr-3">
            <option value="uploaded_at_desc" <?= $sort == 'uploaded_at_desc' ? 'selected' : '' ?>>Newest</option>
            <option value="uploaded_at_asc" <?= $sort == 'uploaded_at_asc' ? 'selected' : '' ?>>Oldest</option>
            <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>A-Z</option>
            <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Z-A</option>
        </select>
        <label class="mr-2">View as:</label>
        <select name="view" onchange="this.form.submit()" class="form-control">
            <option value="grid" <?= $view == 'grid' ? 'selected' : '' ?>>Grid</option>
            <option value="list" <?= $view == 'list' ? 'selected' : '' ?>>List</option>
        </select>
    </form>

    <?php if (empty($files)): ?>
        <p>No files found in this folder.</p>
    <?php else: ?>
        <?php foreach ($files as $file): ?>
            <?php
            $fileName = $file['file_name'];
            $fileUrl = htmlspecialchars($file['file_path']);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $viewer = $fileUrl;

            $googleViewer = "https://docs.google.com/gview?url=" . urlencode($fileUrl) . "&embedded=true";

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'jfif'])) {
                $thumb = "<img src='$fileUrl' class='preview-img' onclick=\"showImageModal('$fileUrl')\">";
                $viewLink = $fileUrl;
            } elseif (in_array($ext, ['pdf', 'docx', 'xlsx', 'txt', 'pptx'])) {
                $thumb = "<iframe src='$googleViewer' class='doc-preview' frameborder='0'></iframe>";
                $viewLink = $googleViewer;
            } else {
                $thumb = "<div class='doc-preview d-flex align-items-center justify-content-center text-muted'>No preview</div>";
                $viewLink = $fileUrl;
            }
            ?>

            <?php if ($view === 'grid'): ?>
                <div class="card file-card">
                    <div class="card-body p-2">
                        <?= $thumb ?>
                        <div class="file-meta">
                            <strong><?= htmlspecialchars($fileName) ?></strong><br>
                            <small><?= htmlspecialchars($file['uploaded_at']) ?></small>
                        </div>
                        <button class="three-dots" onclick="toggleDropdown(this)"><i class="fas fa-ellipsis-v"></i></button>
                        <div class="dropdown-actions">
                            <a href="<?= $viewLink ?>" target="_blank">View File</a>
                            <a href="download.php?url=<?= urlencode($fileUrl) ?>&name=<?= urlencode($fileName) ?>">Download</a>
                            <a href="update.php?id=<?= $file['id'] ?>">Update</a>
                            <a href="delete.php?id=<?= $file['id'] ?>" onclick="return confirm('Delete this file?');">Delete</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="file-row">
                    <?= $thumb ?>
                    <div class="file-info">
                        <strong><?= htmlspecialchars($fileName) ?></strong><br>
                        <small><?= htmlspecialchars($file['uploaded_at']) ?></small><br>
                        <a href="<?= $viewLink ?>" target="_blank" class="btn btn-sm btn-secondary mt-1">View File</a>
                        <a href="download.php?url=<?= urlencode($fileUrl) ?>&name=<?= urlencode($fileName) ?>" class="btn btn-sm btn-outline-success">Download</a>
                        <a href="update.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-warning">Update</a>
                        <a href="delete.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this file?');">Delete</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="add-file-card" onclick="window.location.href='upload_form.php?folder=<?= urlencode($folderName) ?>'">+</div>
</div>

<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content bg-light">
      <div class="modal-body text-center">
        <img id="modalImage" src="" class="img-fluid" style="max-height: 80vh;">
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showImageModal(url) {
    document.getElementById('modalImage').src = url;
    $('#imagePreviewModal').modal('show');
}
function toggleDropdown(btn) {
    $(".dropdown-actions").not($(btn).next()).hide();
    $(btn).next().toggle();
    event.stopPropagation();
}
$(document).click(function () {
    $(".dropdown-actions").hide();
});
</script>
</body>
</html>
