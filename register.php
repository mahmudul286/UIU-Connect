<?php
require 'includes/db_connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $department = $_POST['department'];

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, department) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $role, $department);
    
    if ($stmt->execute()) { header("Location: login.php?msg=registered"); exit(); } 
    else { $error = "Email already exists!"; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - UIU Connect</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .auth-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: var(--bg-light); padding: 20px; }
        .auth-card { width: 100%; max-width: 450px; padding: 40px; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="card auth-card">
            <h2 style="font-size: 20px; margin-bottom: 25px; text-align: center;">Create Account</h2>
            
            <?php if(isset($error)): ?>
                <div style="background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; font-size: 14px; margin-bottom: 15px; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="full_name" class="form-input" placeholder="Full Name" required>
                <input type="email" name="email" class="form-input" placeholder="University Email" required>
                <input type="password" name="password" class="form-input" placeholder="Create Password" required>
                
                <div style="display: flex; gap: 15px;">
                    <select name="role" class="form-input" required>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                    </select>
                    <input type="text" name="department" class="form-input" placeholder="Dept (e.g., CSE)" required>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Register</button>
            </form>
            
            <p style="text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--uiu-orange); font-weight: 500;">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>