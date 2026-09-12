<?php
$user_name = $_SESSION['full_name'] ?? 'Unknown User';
$user_role = $_SESSION['role'] ?? 'Student';
$department = $_SESSION['department'] ?? '';
$words = explode(" ", $user_name);
$initials = strtoupper($words[0][0] . (isset($words[1]) ? $words[1][0] : ''));
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="user-info">
        <div class="avatar-placeholder" style="background: linear-gradient(135deg, var(--uiu-orange) 0%, #d95316 100%); color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; margin: 10px auto 12px auto;"><?php echo $initials; ?></div>
        <p style="font-size: 16px; font-weight: 600; color: var(--text-main); margin-bottom: 4px; text-align: center;"><?php echo htmlspecialchars($user_name); ?></p>
        <p style="color: var(--text-muted); font-size: 13px; font-weight: 500; text-align: center;">
            <?php echo htmlspecialchars($user_role) . " • " . htmlspecialchars($department); ?>
        </p>
    </div>

    
    <nav class="nav-menu">
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-house" style="width: 20px; font-size: 18px; text-align: center;"></i> Dashboard
        </a>
        <a href="courses.php" class="<?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-book-open" style="width: 20px; font-size: 18px; text-align: center;"></i> Materials Hub
        </a>
        <a href="skills.php" class="<?php echo ($current_page == 'skills.php') ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-code" style="width: 20px; font-size: 18px; text-align: center;"></i> Skills & Dev
        </a>
        <a href="counseling.php" class="<?php echo ($current_page == 'counseling.php') ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-user-group" style="width: 20px; font-size: 18px; text-align: center;"></i> Counseling
        </a>
        <a href="blood_bank.php" class="<?php echo ($current_page == 'blood_bank.php') ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-droplet" style="width: 20px; font-size: 18px; text-align: center;"></i> Blood Bank
        </a>
        <a href="marketplace.php" class="<?php echo ($current_page == 'marketplace.php') ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-store" style="width: 20px; font-size: 18px; text-align: center;"></i> Marketplace
        </a>
        <a href="clubs.php" class="<?php echo ($current_page == 'clubs.php') ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-users" style="width: 20px; font-size: 18px; text-align: center;"></i> Clubs & Societies
        </a>
        <a href="alumni.php" class="<?php echo ($current_page == 'alumni.php') ? 'active' : ''; ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-briefcase" style="width: 20px; font-size: 18px; text-align: center;"></i> Jobs & Alumni
        </a>
    </nav>

    <div class="bottom-menu" style="margin-top: auto; padding: 20px 0; border-top: 1px solid var(--border-light);">
        <a href="profile.php" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: var(--text-main); text-decoration: none; font-weight: 500;">
            <i class="fa-regular fa-user" style="width: 20px; font-size: 18px; text-align: center;"></i> My Profile
        </a>
        <a href="auth/logout.php" style="display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: #EF4444; text-decoration: none; font-weight: 500;">
            <i class="fa-solid fa-arrow-right-from-bracket" style="width: 20px; font-size: 18px; text-align: center;"></i> Logout
        </a>
    </div>
</aside>