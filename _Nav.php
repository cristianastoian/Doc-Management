<?php include 'side.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doc Management</title>
  
 
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <style>
    .navbar {
      margin-bottom: 20px;
      box-shadow: 0 4px 30px rgba(0,0,0,0.1);
    }

    .gradient-navbar {
      background-image: linear-gradient(135deg, rgb(130, 95, 212), #fbc2eb); 
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); 
    }

    .logo {
      color: white;
      font-size: 1.5rem;
      font-weight: bold;
    }

    .logo:hover {
      color:  rgb(51, 50, 50);
    }

    .nav-link {
      color: white !important;
      transition: color 0.3s ease-in-out;
    }

    .nav-link:hover {
      color: rgb(51, 50, 50) !important;
    }

    .form-inline input[type="search"] {
      border-radius: 4px;
      border: none;
      padding: 20px 20px;
      margin-left: 10px;
      margin-top: 10px;
    }

    .form-inline .btn {
      margin-left: 4px;
      margin-top: 10px;
    }

    .sidebar {
      height: 100%;
      width: 0;
      position: fixed;
      z-index: 1500;
      top: 0;
      left: 0;
      background-image: linear-gradient(135deg, rgb(130, 95, 212), #fbc2eb); 
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2); 
      overflow-x: hidden;
      transition: 0.3s;
      padding-top: 60px;
    }

    .sidebar a {
      padding: 12px 20px;
      text-decoration: none;
      font-size: 18px;
      color: white;
      display: block;
      transition: 0.2s;
    }

    .sidebar a:hover {
      background-color:rgb(135, 134, 134);
        color: rgb(51, 50, 50) !important;
      
    }

    .sidebar .closebtn {
      position: absolute;
      top: 0;
      right: 12px;
      font-size: 32px;
    }
  </style>
</head>

<body>


<?php include 'side.php'; ?>


<nav class="navbar navbar-expand-lg gradient-navbar">

 
  <button class="btn btn-light mr-3" onclick="openSidebar()">
    <i class="fas fa-bars"></i>
  </button>


  <a class="navbar-brand logo" href="index.php"> DOC MANAGEMENT <i class="fas fa-cloud-upload-alt"></i></a>


  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>


  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ml-auto">
      <?php if (isset($_SESSION['user_email'])): ?>
        <li class="nav-item">
          <span class="nav-link text-white">
            👤 <?php echo htmlspecialchars($_SESSION['user_email']); ?>
          </span>
        </li>
      <?php endif; ?>

      <li class="nav-item">
        <a class="nav-link text-white" href="upload_form.php"><i class="fas fa-upload"></i> Upload File</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="view_files.php"><i class="fas fa-file-alt"></i> View Files</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </li>
    </ul>


    <form class="form-inline ml-3" action="view_files.php" method="get">
      <input class="form-control" type="search" name="search" placeholder="Search files..." aria-label="Search">
      <button class="btn btn-light" type="submit"><i class="fas fa-search"></i></button>
    </form>
  </div>
</nav>


<script>
function openSidebar() {
  document.getElementById("mySidebar").style.width = "250px";
}
function closeSidebar() {
  document.getElementById("mySidebar").style.width = "0";
}
</script>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
