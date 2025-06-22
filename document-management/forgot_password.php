<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $reset_link = "http://localhost/document-management/reset_password.php?email=" . urlencode($email);
        mail($email, "Password Reset", "Click the link to reset your password: $reset_link");

        $message = "<div class='alert alert-success'>Reset link sent to your email.</div>";
    } else {
        $message = "<div class='alert alert-danger'>No account found with that email.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
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
    <h4 class="mb-4 text-center">Forgot Your Password?</h4>

    <?php if (isset($message)) echo $message; ?>

    <form method="POST">
        <div class="form-group">
            <label>Enter your email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button class="btn btn-primary btn-block">Send Reset Link</button>
        <a href="login.php" class="btn btn-outline-secondary btn-block mt-2">← Back to Login</a>
    </form>
</div>
</body>
</html>
