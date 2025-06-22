<?php
include './conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $password);
    
    if ($stmt->execute()) {
        echo "<script>alert('User registered!'); window.location.href='login.php';</script>";
    } else {
        echo "<div style='color:red;'>Error: " . $stmt->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .register-container {
            background-color: #fff0f4;
            border-radius: 10px;
            padding: 30px;
            margin-top: 60px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .btn-grey {
            background-color: #d6d8db;
            border-color: #c4c6c8;
            color: #000;
            font-weight: 500;
        }

        .btn-grey:hover {
            background-color: #c0c2c4;
            border-color: #b0b2b4;
        }

        .requirements {
            font-size: 0.85rem;
            color: #dc3545;
            margin-top: 4px;
            display: none;
        }

        .requirement.valid {
            color: #28a745;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 38px;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">
<div class="container" style="max-width: 500px;">
    <div class="register-container">
        <h2 class="mb-4 text-center">Create an Account</h2>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group position-relative">
                <label>Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <span class="password-toggle" onclick="togglePassword()">👁️</span>
                <div id="password-requirements" class="requirements">
                    <div id="length" class="requirement">• At least 8 characters</div>
                    <div id="uppercase" class="requirement">• At least one uppercase letter</div>
                    <div id="number" class="requirement">• At least one number</div>
                    <div id="special" class="requirement">• At least one special character</div>
                </div>
            </div>

            <button class="btn btn-grey btn-block">Register</button>
            <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</div>

<script>
    const passwordInput = document.getElementById("password");
    const requirements = document.getElementById("password-requirements");

    passwordInput.addEventListener("focus", function () {
        requirements.style.display = "block";
    });

    passwordInput.addEventListener("input", function () {
        const val = passwordInput.value;
        document.getElementById("length").classList.toggle("valid", val.length >= 8);
        document.getElementById("uppercase").classList.toggle("valid", /[A-Z]/.test(val));
        document.getElementById("number").classList.toggle("valid", /[0-9]/.test(val));
        document.getElementById("special").classList.toggle("valid", /[^A-Za-z0-9]/.test(val));
    });

    function togglePassword() {
        const pwd = document.getElementById("password");
        pwd.type = pwd.type === "password" ? "text" : "password";
    }
</script>
</body>
</html>
