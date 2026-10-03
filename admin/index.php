<?php
require_once '../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FIX: Update redirect target
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'Admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email' AND role = 'Admin'");
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // FIX: Redirect to new admin dashboard file
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Unauthorized access! You are not an Admin.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - UIU Connect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: #0F172A; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: #1E293B; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 1px solid #334155; }
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-area i { font-size: 40px; color: #F97316; margin-bottom: 15px; }
        .logo-area h2 { margin: 0; font-size: 22px; color: #F8FAFC; }
        .logo-area p { margin: 5px 0 0 0; font-size: 13px; color: #94A3B8; text-transform: uppercase; letter-spacing: 1px; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 13px; color: #CBD5E1; margin-bottom: 8px; font-weight: 500; }
        .input-group input { width: 100%; padding: 12px; background: #0F172A; border: 1px solid #334155; border-radius: 8px; color: white; font-size: 14px; outline: none; box-sizing: border-box; transition: 0.3s; }
        .input-group input:focus { border-color: #F97316; box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2); }
        .btn-admin { width: 100%; background: #F97316; color: white; border: none; padding: 12px; font-size: 15px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .btn-admin:hover { background: #EA580C; }
        .error-msg { background: #7F1D1D; color: #FECACA; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; text-align: center; border: 1px solid #991B1B; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo-area">
            <i class="fa-solid fa-shield-halved"></i>
            <h2>UIU Connect</h2>
            <p>Admin Portal</p>
        </div>

        <?php if($error): ?>
            <div class="error-msg"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Admin Email</label>
                <input type="email" name="email" placeholder="admin@uiu.ac.bd" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-admin">Secure Login <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left: 5px;"></i></button>
        </form>
    </div>
</body>
</html>