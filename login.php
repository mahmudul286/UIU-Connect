<?php
require_once 'includes/db_connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT id, full_name, password_hash, role, department FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['department'] = $user['department'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - UIU Connect</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-light);
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }

        .auth-logo {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--text-main);
        }

        .auth-logo span {
            color: var(--uiu-orange);
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="card auth-card">
            <div class="auth-logo">UIU<span>Connect</span></div>
            <h2 style="font-size: 20px; margin-bottom: 20px; text-align: center;">Welcome Back</h2>

            <?php if (isset($error)): ?>
                <div
                    style="background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; font-size: 14px; margin-bottom: 15px; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label
                    style="font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; display: block;">University
                    Email</label>
                <input type="email" name="email" class="form-input" placeholder="e.g.@bscse.uiu.ac.bd" required>

                <label
                    style="font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; display: block;">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter your password" required>

                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Sign In</button>
            </form>

            <p style="text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-muted);">
                New to UIU Connect? <a href="register.php" style="color: var(--uiu-orange); font-weight: 500;">Create an
                    account</a>
            </p>
        </div>
    </div>
</body>

</html>