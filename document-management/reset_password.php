<?php
include 'conn.php';

$email = $_GET['email'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && $email) {
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $newPassword, $email);
    $stmt->execute();

    echo "<script>alert('Password updated!'); window.location.href='login.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to bottom right, #fce4ec, #f8f9fa);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .box {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
<div class="box">
    <h4 class="mb-4 text-center">Reset Your Password</h4>

    <form method="POST">
        <div class="form-group">
            <label>New Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-success btn-block">Reset Password</button>
        <a href="login.php" class="btn btn-outline-secondary btn-block mt-2">← Back to Login</a>
    </form>
</div>
</body>
</html>
