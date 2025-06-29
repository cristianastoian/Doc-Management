<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

include '_Nav.php';
include './conn.php';

$sort = $_GET['sort'] ?? 'name_asc';
$view = $_GET['view'] ?? 'list';
$searchQuery = trim($_GET['search'] ?? '');

$imageMap = [
    'red' => 'folder-icon.png',
    'yellow' => 'folder-icon-yellow.png',
    'blue' => 'folder-icon-blue.png',
    'grey' => 'folder-icon-grey.png'
];

switch ($sort) {
    case 'name_desc': 
        $orderBy = "file_name DESC"; 
        $orderClause = "ORDER BY name DESC"; 
        break;
    case 'uploaded_at_asc': 
        $orderBy = "uploaded_at ASC"; 
        $orderClause = "ORDER BY created_at ASC"; 
        break;
    case 'uploaded_at_desc': 
        $orderBy = "uploaded_at DESC"; 
        $orderClause = "ORDER BY created_at DESC"; 
        break;
    default: 
        $orderBy = "file_name ASC"; 
        $orderClause = "ORDER BY name ASC"; 
        break;
}

$folders = [];
if ($searchQuery !== '') {
    $query = "SELECT file_name, file_path, folder, uploaded_at FROM uploads WHERE user_id = ? AND file_name LIKE ? ORDER BY $orderBy";
    $stmt = $conn->prepare($query);
    $like = "%" . $searchQuery . "%";
    $stmt->bind_param("is", $user_id, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $files = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $sql = "SELECT id, name AS folder, folder_color, created_at FROM folders WHERE user_id = $user_id $orderClause";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $folders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Files</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .pretty-heading {
      font-size: 1.8rem;
      font-weight: 700;
      color: #777;
      background: white;
      padding: 10px 20px;
      border-radius: 6px;
      border: 1px solid #ccc;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      margin-bottom: 25px;
    }
    .folder-list-item {
      display: flex;
      align-items: center;
      background: white;
      padding: 10px 15px;
      margin: 10px 0;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .folder-list-item img {
      width: 40px;
      height: auto;
      margin-right: 15px;
    }
    .folder-list-item a {
      color: #333;
      text-decoration: none;
      font-weight: 500;
    }
    .folder-list-item a:hover {
      color: #555;
      text-decoration: none;
    }
    .file-card {
      background: white;
      padding: 15px;
      margin: 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      width: 250px;
      display: inline-block;
      vertical-align: top;
      text-align: center;
    }
    .edit-btn-popup {
      position: absolute;
      display: none;
      background-color: #e0e0e0;
      color: #000;
      padding: 10px 16px;
      font-size: 1rem;
      font-weight: 500;
      border-radius: 6px;
      cursor: pointer;
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>
<div class="container mt-4">
  <h2 class="pretty-heading">
    <?= $searchQuery ? "Search results for \"" . htmlspecialchars($searchQuery) . "\"" : "Your Folders" ?>
  </h2>

  <form method="GET" class="form-inline mb-4">
    <input type="hidden" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
    <label class="mr-2">Sort by:</label>
    <select name="sort" onchange="this.form.submit()" class="form-control mr-3">
      <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>A-Z</option>
      <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Z-A</option>
      <option value="uploaded_at_desc" <?= $sort === 'uploaded_at_desc' ? 'selected' : '' ?>>Newest</option>
      <option value="uploaded_at_asc" <?= $sort === 'uploaded_at_asc' ? 'selected' : '' ?>>Oldest</option>
    </select>
    <label class="mr-2">View:</label>
    <select name="view" onchange="this.form.submit()" class="form-control">
      <option value="list" <?= $view === 'list' ? 'selected' : '' ?>>List</option>
      <option value="grid" <?= $view === 'grid' ? 'selected' : '' ?>>Grid</option>
    </select>
  </form>

  <?php if ($searchQuery): ?>
    <?php if (empty($files)): ?>
      <p>No files matched your search.</p>
    <?php else: ?>
      <?php foreach ($files as $file): ?>
        <?php
        $fileName = htmlspecialchars($file['file_name']);
        $fileUrl = htmlspecialchars($file['file_path']);
        $uploadedAt = htmlspecialchars($file['uploaded_at']);
        ?>
        <div class="file-card">
          <p><strong><?= $fileName ?></strong></p>
          <small><?= $uploadedAt ?></small><br>
          <a href="download.php?url=<?= urlencode($fileUrl) ?>&name=<?= urlencode($fileName) ?>" class="btn btn-sm btn-secondary mt-2">Download</a>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php else: ?>
    <?php foreach ($folders as $folder): ?>
      <?php
        $folderName = $folder['folder'] ?: 'Uncategorized';
        $folderColor = $folder['folder_color'] ?? 'red';
        $folderId = $folder['id'];
        $icon = $imageMap[$folderColor] ?? 'folder-icon.png';
      ?>
      <?php if ($view === 'list'): ?>
        <div class="folder-list-item"
             oncontextmenu="showEditButton(event, this, <?= $folderId ?>, '<?= htmlspecialchars($folderName) ?>', '<?= htmlspecialchars($folderColor) ?>')">
          <img src="<?= htmlspecialchars($icon) ?>" alt="Folder Icon">
          <a href="view_folder.php?folder=<?= urlencode($folderName) ?>"><?= htmlspecialchars($folderName) ?></a>
        </div>
      <?php else: ?>
        <div class="file-card"
             oncontextmenu="showEditButton(event, this, <?= $folderId ?>, '<?= htmlspecialchars($folderName) ?>', '<?= htmlspecialchars($folderColor) ?>')">
          <img src="<?= htmlspecialchars($icon) ?>" alt="Folder Icon" style="width: 80px;">
          <p><strong><a href="view_folder.php?folder=<?= urlencode($folderName) ?>" style="text-decoration:none; color:#333;">
            <?= htmlspecialchars($folderName) ?></a></strong></p>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div id="edit-popup" class="edit-btn-popup" onclick="openEditModal()">Edit</div>

<script>
let currentFolder = {};

function showEditButton(event, element, id, name, color) {
  event.preventDefault();
  currentFolder = { id, name, color };
  const popup = document.getElementById('edit-popup');
  const rect = element.getBoundingClientRect();
  popup.style.display = 'block';
  popup.style.top = window.scrollY + rect.top + 10 + 'px';
  popup.style.left = window.scrollX + rect.right + 10 + 'px';
}

function openEditModal() {
  if (currentFolder.id) {
    window.location.href = 'edit_folder.php?folder_id=' + currentFolder.id;
  }
}

document.addEventListener('click', function (e) {
  const popup = document.getElementById('edit-popup');
  if (!popup.contains(e.target)) {
    popup.style.display = 'none';
  }
});
</script>
</body>
</html>
