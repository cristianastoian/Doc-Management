<?php
session_start();
include 'conn.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            header("Location: index.php");  
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to bottom right, #fce4ec, #f8f9fa);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-box {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .login-box h2 {
            color: #d63384;
            font-weight: 600;
        }
        .btn-pink {
            background-color: #f7a8c2;
            border-color: #f7a8c2;
            color: white;
            font-weight: 500;
        }
        .btn-pink:hover {
            background-color: #e26d95;
            border-color: #e26d95;
        }
        a {
            color: #d63384;
        }
    </style>
</head>
<body>
<div class="login-box">
    <h2 class="text-center mb-4">Login</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
      <div class="form-group position-relative">
    <label>Password</label>
    <input type="password" name="password" id="password" class="form-control pr-5" required>
    <span onclick="togglePassword()" style="position: absolute; right: 15px; top: 38px; cursor: pointer;">
        👁️
    </span>
</div>

       
        <button class="btn btn-pink btn-block">Login</button>
    </form>

    <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    pwd.type = pwd.type === "password" ? "text" : "password";
}
</script>

