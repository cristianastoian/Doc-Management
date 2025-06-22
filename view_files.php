<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

include '_Nav.php';
include './conn.php';

$searchQuery = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'name_asc';
$view = $_GET['view'] ?? 'list';

switch ($sort) {
    case 'name_desc': $orderBy = "file_name DESC"; break;
    case 'uploaded_at_asc': $orderBy = "uploaded_at ASC"; break;
    case 'uploaded_at_desc': $orderBy = "uploaded_at DESC"; break;
    default: $orderBy = "file_name ASC"; break;
}

$imageMap = [
    'red' => 'folder-icon.png',
    'yellow' => 'folder-icon-yellow.png',
    'blue' => 'folder-icon-blue.png',
    'grey' => 'folder-icon-grey.png'
];

if ($searchQuery !== '') {
    $query = "SELECT file_name, file_path, folder, uploaded_at FROM uploads WHERE user_id = ? AND file_name LIKE ? ORDER BY $orderBy";
    $stmt = $conn->prepare($query);
    $like = "%" . $searchQuery . "%";
    $stmt->bind_param("is", $user_id, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $files = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $sql = "SELECT id, name AS folder, folder_color FROM folders WHERE user_id = $user_id ORDER BY name ASC";
    $result = $conn->query($sql);
    $folders = [];
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
      transition: background 0.2s ease;
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

    .file-card img {
      max-width: 100%;
      height: auto;
      border-radius: 4px;
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
      white-space: nowrap;
    }
  </style>
</head>
<body>
<div class="container mt-4">
  <h2 class="pretty-heading">
    <?= $searchQuery ? "Search results for \"" . htmlspecialchars($searchQuery) . "\"" : "Your Folders" ?>
  </h2>

  <?php if ($searchQuery): ?>
    <?php if (empty($files)): ?>
      <p>No files matched your search.</p>
    <?php else: ?>
      <?php foreach ($files as $file): ?>
        <?php
          $fileName = htmlspecialchars($file['file_name']);
          $fileUrl = htmlspecialchars($file['file_path']);
          $uploadedAt = htmlspecialchars($file['uploaded_at']);
          $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

          $preview = '';
          if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
              $preview = "<img src='$fileUrl' alt='preview'>";
          } elseif ($fileExt === 'pdf') {
              $preview = "<a href='$fileUrl' target='_blank'>View PDF</a>";
          } else {
              $preview = "<p>$fileName</p>";
          }
        ?>
        <div class="file-card">
            <?= $preview ?>
            <p><strong><?= $fileName ?></strong></p>
            <small><?= $uploadedAt ?></small><br>
            <a href="download.php?url=<?= urlencode($fileUrl) ?>&name=<?= urlencode($fileName) ?>" class="btn btn-sm btn-secondary mt-2">Download</a>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php else: ?>
    <form method="GET" class="form-inline mb-4">
      <label class="mr-2">Sort by:</label>
      <select name="sort" onchange="this.form.submit()" class="form-control mr-3">
          <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>A-Z</option>
          <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Z-A</option>
          <option value="uploaded_at_desc" <?= $sort === 'uploaded_at_desc' ? 'selected' : '' ?>>Newest</option>
          <option value="uploaded_at_asc" <?= $sort === 'uploaded_at_asc' ? 'selected' : '' ?>>Oldest</option>
      </select>
      <label class="mr-2">View:</label>
      <select name="view" onchange="this.form.submit()" class="form-control">
          <option value="list" selected>List</option>
          <option value="grid">Grid</option>
      </select>
    </form>

    <?php foreach ($folders as $folder): ?>
      <?php
        $folderName = $folder['folder'] ?: 'Uncategorized';
        $folderColor = $folder['folder_color'] ?? 'red';
        $folderId = $folder['id'];
        $icon = $imageMap[$folderColor] ?? 'folder-icon.png';
      ?>
      <div class="folder-list-item"
           oncontextmenu="showEditButton(event, this, <?= $folderId ?>, '<?= htmlspecialchars($folderName) ?>', '<?= htmlspecialchars($folderColor) ?>')">
        <img src="<?= htmlspecialchars($icon) ?>" alt="Folder Icon">
        <a href="view_folder.php?folder=<?= urlencode($folderName) ?>"><?= htmlspecialchars($folderName) ?></a>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Edit popup and modal code stays unchanged -->
<!-- JS for edit popup/modal stays unchanged -->
</body>
</html>
