<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['user_email']) && !isset($_SESSION['welcome_shown'])) {
    echo "<script>alert('Logged in as: " . $_SESSION['user_email'] . "');</script>";
    $_SESSION['welcome_shown'] = true;
}

include '_Nav.php';
include 'conn.php';

$userEmail = $_SESSION['user_email'];
$greetingName = ucfirst(explode('@', $userEmail)[0]);
$cleanedName = preg_replace('/[0-9]/', '', $greetingName); 
$greetingName = ucfirst($cleanedName);

$recentFolders = [];
$stmt = $conn->prepare("SELECT name, folder_color, created_at FROM folders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentFolders[] = $row;
}

$user_id = $_SESSION['user_id'];

$totalFiles = 0;
$res = $conn->query("SELECT COUNT(*) AS count FROM uploads WHERE user_id = $user_id");
if ($row = $res->fetch_assoc()) {
    $totalFiles = $row['count'];
}

$filesToday = 0;
$res = $conn->query("SELECT COUNT(*) AS count FROM uploads WHERE user_id = $user_id AND DATE(uploaded_at) = CURDATE()");
if ($row = $res->fetch_assoc()) {
    $filesToday = $row['count'];
}


$typeCounts = [
    'pdf' => 0,
    'doc' => 0,
    'docx' => 0,
    'xlsx' => 0,
    'png' => 0
];
$totalTypes = 0;

$res = $conn->query("SELECT file_name FROM uploads WHERE user_id = $user_id");
while ($row = $res->fetch_assoc()) {
    $ext = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
    if (isset($typeCounts[$ext])) {
        $typeCounts[$ext]++;
         $totalTypes++;
    }
}
$typePercents = [];
foreach ($typeCounts as $type => $count) {
    $typePercents[$type] = $totalTypes > 0 ? round(($count / $totalTypes) * 100) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>File Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <style>
    body { background-color:rgb(247, 244, 244); font-family: 'Segoe UI', sans-serif; }


.hero {
  background-color:rgb(241, 239, 240);
  color: rgb(107, 103, 103);
  padding: 30px 30px 60px;
  border-radius: 0 200px 90px 20px;
  width: 100%;
  position: relative;
  z-index: 1;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

    .hero h1 { 
      font-size: 2.9rem;
      font-weight: 700;
      margin-bottom: 10px;
      transition: transform 0.3s ease;
     }
    .hero p { font-size: 1.1rem; margin-bottom: 20px; color: #333; }

   

    .hero .btn {
      margin: 5px;
      font-size: 1rem;
      padding: 10px 20px;
    }
.wave-text {
  display: inline-block;
  font-size: 2rem;
  font-weight: bold;
  cursor: default;
}

.wave-text span {
  display: inline-block;
  transition: transform 0.3s ease;
}

.wave-text:hover span {
  animation: waveJump 0.6s ease forwards;
  animation-delay: calc(var(--i) * 0.05s);
}

@keyframes waveJump {
  0% { transform: translateY(0); }
  30% { transform: translateY(-10px); }
  60% { transform: translateY(5px); }
  100% { transform: translateY(0); }
}


.stats {
  display: flex;
  gap: 20px;
  margin-top: 40px;
  flex-wrap: wrap;
  justify-content: left;
  z-index: 1;
  position: relative;
 
}
  
.stat-box {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  padding: 20px;
  text-align: center;
  color: #444;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.dashboard-row {
  position: relative;
  margin-top: 0;
  padding: 0 30px 40px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
    .stat-box h5 {
      font-size: 1.2rem;
      margin-bottom: 10px;
      color:rgb(197, 128, 215);
    }

    .stat-box p {
      font-size: 1rem;
      font-weight: 500;
    }


.chart-container {
  position: absolute;
  top: 20px;
  right: 30px;
  width: 400px;
  height: 400px;
  background: #fff;
  border-radius: 50px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
  padding: 70px;
  z-index: 2;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 4px 30px rgba(0,0,0,0.1);
}


    .recent-section {
      margin-top: 50px;
    }
   


canvas#fileTypeChart {
  width: 280px !important;
  height: 280px !important;
  max-width: 310px !important;
  max-height: 280px !important;
  min-width: 280px !important;
  min-height: 280px !important;
  transition: none !important;
  transform: none !important;
}



    .folder-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      transition: transform 0.2s;
    }
    .folder-card:hover { transform: scale(1.03); }

    .folder-card img { width: 80px; height: auto; }
    .folder-card p { margin-top: 10px; font-weight: 500; }
    .folder-card small { color: #777; }

    #ai-bubble {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background-color: rgb(239, 196, 246);
      color: #555;
      font-size: 24px;
      font-weight: bold;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
      z-index: 1001;
      transition: transform 0.3s ease;
    }
    #ai-bubble:hover { transform: scale(1.1); }

    #ai-bubble .dot {
      animation: blink 1.2s infinite;
      margin: 0 1px;
    }
    #ai-bubble .dot:nth-child(2) { animation-delay: 0.2s; }
    #ai-bubble .dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes blink {
      0%, 20% { opacity: 0.2; }
      50% { opacity: 1; }
      100% { opacity: 0.2; }
    }

 #chatbox {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 300px;
  background: #fff;
  border-radius: 10px;
  padding: 15px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  z-index: 1000;
  font-size: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  transition: width 0.3s ease, height 0.3s ease;
  max-height: 300px;
}

#chatbox.expanded {
  width: 1000px;
  
  max-height: 1000px;
}
#chat-toggle {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 22px;
  height: 22px;
  background-color: white;
  border: 1px solid pink;
  color: black;
  font-size: 14px;
  font-weight: bold;
  text-align: center;
  line-height: 22px;
  border-radius: 3px;
  cursor: pointer;
  z-index: 10;
  display: none; 
}

    .chat-title {
      margin-bottom: 10px;
      font-weight: bold;
    }

    .chat-box {
      height: auto;
      background: #f8f9fa;
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 8px;
      overflow-y: auto;
      white-space: pre-line;
    }

    .chat-input-group {
      display: flex;
      margin-top: 11px;
    }

    #chat-input {
      flex: 1;
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 5px 0 0 5px;
    }

    .chat-send-btn {
      padding: 6px 12px;
      border: none;
      background-color:  rgb(239, 196, 246);
      color: white;
      border-radius: 0 5px 5px 0;
      cursor: pointer;
    }

    .chat-send-btn:hover {
      background-color: rgb(211, 174, 217);
    }
    #chat-response {
  max-height: 200px;        
  overflow-y: auto;
  white-space: pre-line;
  padding-right: 10px;
}

  </style>
</head>
<body>


<div class="dashboard-row">

  <div class="hero">
   <h1 class="wave-text">
  <?php 
    $text = "Welcome, " ."" . htmlspecialchars($greetingName) . "!";
    $chars = str_split($text);
    foreach ($chars as $i => $char) {
        echo "<span style='--i:$i'>$char</span>";
    }
  ?>
</h1>
    <p>Upload your documents, create folders and organize them!</p>
    <a href="upload_form.php" class="btn btn-light"><i class="fas fa-upload"></i> Upload File</a>
    <a href="create_folder.php" class="btn btn-light"><i class="fas fa-folder-plus"></i> Create Folder</a>
    <a href="view_files.php" class="btn btn-light"><i class="fas fa-folder-open"></i> View Files</a>
  </div>

  <div class="chart-container">
    <canvas id="fileTypeChart"></canvas>
  </div>

  <div class="stats">
    <div class="stat-box">
  <h5>Total Files</h5>
  <p><?= $totalFiles ?></p>
</div>
<div class="stat-box">
  <h5>Total Folders</h5>
  <p><?= count($recentFolders) ?></p>
</div>
<div class="stat-box">
  <h5>Uploaded Today</h5>
  <p><?= $filesToday ?> files</p>
</div>
<div class="stat-box">
  <h5>PDF / Word / Excel</h5>
  <p>
    <?= $typePercents['pdf'] ?? 0 ?>% /
    <?= ($typePercents['doc'] ?? 0) + ($typePercents['docx'] ?? 0) ?>% /
    <?= $typePercents['xlsx'] ?? 0 ?>%
  </p>
</div>


  </div>

</div>

<?php if (!empty($recentFolders)): ?>
<div class="container recent-section">
  <h4 class="mb-4">Recently Created Folders</h4>
  <div class="row">
    <?php foreach ($recentFolders as $folder): ?>
      <?php
        $color = $folder['folder_color'];
        $iconMap = [
            'red' => 'folder-icon.png',
            'yellow' => 'folder-icon-yellow.png',
            'blue' => 'folder-icon-blue.png',
            'grey' => 'folder-icon-grey.png',
            'pink' => 'folder-icon.png'
        ];
        $icon = $iconMap[$color] ?? 'folder-icon.png';
      ?>
      <div class="col-md-3 mb-4">
        <a href="view_folder.php?folder=<?= urlencode($folder['name']) ?>" style="text-decoration: none; color: inherit;">
        <div class="folder-card">
          <img src="<?= $icon ?>" alt="Folder">
          <p><?= htmlspecialchars($folder['name']) ?></p>
          <small>Created: <?= date("Y-m-d H:i", strtotime($folder['created_at'])) ?></small>
        </div>
       </div>
     
       </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>


<div id="ai-bubble" onclick="toggleChatbox()">
  <span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
</div>

<div id="chatbox" class="collapsed" style="display: none;">
  <div id="chat-toggle" onclick="toggleChatSize()" style="display: none;">[ ]</div>
  <div class="chat-title">Need help?</div>
  <div id="chat-response" class="chat-box">Ask me how to use this app!</div>
  <div class="chat-input-group">
    <input type="text" id="chat-input" placeholder="Ask something...">
    <button onclick="sendMessage()" class="chat-send-btn">Send</button>
  </div>
</div>


<script>
function toggleChatbox() {
  const box = document.getElementById('chatbox');
  const toggleBtn = document.getElementById('chat-toggle');

  if (box.style.display === 'none' || box.style.display === '') {
    box.style.display = 'block';
    toggleBtn.style.display = 'block'; 
  } else {
    box.style.display = 'none';
    toggleBtn.style.display = 'none'; 
  }
}

function toggleChatSize() {
  const box = document.getElementById('chatbox');
  box.classList.toggle('expanded');
}


function sendMessage() {
  const input = document.getElementById('chat-input');
  const responseBox = document.getElementById('chat-response');
  const message = input.value.trim();
  if (!message) return;

  responseBox.innerText = "Thinking...";

  fetch("http://127.0.0.1:5000/generate", {
    method: "POST",
    headers: { 
      "Content-Type": "application/json",
      "Origin": "http://127.0.0.1"
    },
    body: JSON.stringify({ message: message })
  })
  .then(res => res.json())
  .then(data => {
    if (data.reply) {
      responseBox.innerText = data.reply;
    } else if (data.error) {
      responseBox.innerText = "Server error: " + data.error;
    } else {
      responseBox.innerText = "Unknown response.";
    }
  })
  .catch(err => {
    responseBox.innerText = "Error: " + err.message;
  });

  input.value = "";
}



</script>
<script>
const ctx = document.getElementById('fileTypeChart').getContext('2d');
new Chart(ctx, {
  type: 'pie',
  data: {
    labels: ['PDF', 'Word', 'Excel', 'Png'],
    datasets: [{
      data: [
        <?= $typeCounts['pdf'] ?>,
        <?= $typeCounts['docx'] + $typeCounts['doc'] ?>,
        <?= $typeCounts['xlsx'] ?>,
        <?= $typeCounts['png'] ?>
      ],
      backgroundColor: ['#825FD4', '#A17DF7', '#CBB8FF', '#F3E8FF'],
      borderColor: '#fff',
      borderWidth: 1
    }]
  },
  options: {
    
    plugins: { legend: { position: 'bottom' } }
  }
});
function toggleChatSize() {
  const box = document.getElementById('chatbox');
  box.classList.toggle('expanded');
}

</script>

</body>
</html>