<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$header_name = $_SESSION['full_name'] ?? 'U';
$header_initial = strtoupper($header_name[0]);
$user_id = $_SESSION['user_id'];

$header_user_res = $conn->query("SELECT profile_pic FROM users WHERE id = $user_id");
$header_profile_pic = ($header_user_res && $header_user_res->num_rows > 0) ? $header_user_res->fetch_assoc()['profile_pic'] : null;

// 1. Notification Badge 
$unread_notifs = $conn->query("SELECT COUNT(id) as c FROM notifications WHERE user_id = $user_id AND is_read = 0 AND type IN ('like', 'comment', 'announcement')")->fetch_assoc()['c'];

// 2. Message Badge 
$unread_msgs = $conn->query("SELECT COUNT(id) as c FROM messages WHERE receiver_id = $user_id AND is_read = 0")->fetch_assoc()['c'];
$pending_reqs = $conn->query("SELECT COUNT(id) as c FROM connections WHERE receiver_id = $user_id AND status = 'pending'")->fetch_assoc()['c'];
$total_msg_badge = $unread_msgs + $pending_reqs;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Connect - Campus OS</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <style>
        .badge-count { position: absolute; top: -8px; right: -8px; background: #EF4444; color: white; font-size: 10px; font-weight: 700; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 2px solid #fff; z-index: 10; }
        .fa-sistrix { width: 18px; height: 18px; color: var(--text-muted); position: absolute; left: 15px; top: 50%; transform: translateY(-50%); }
        
        .fa-shake { animation-duration: 2.5s; }
    </style>
</head>
<body>
<header class="top-header">
    <div class="logo">
        <a href="dashboard.php" style="font-size: 24px; font-weight: 700; letter-spacing: -0.5px; text-decoration:none; color:inherit;">
            <strong>UIU</strong><span style="font-weight: 600; color:black;">Connect</span>
        </a>
    </div>
    
    <!-- LIVE SEARCH BAR -->
    <div class="search-bar" style="position: relative;">
        <i class="fa-brands fa-sistrix"></i>
        <input type="text" id="topSearchInput" placeholder="Search users, posts, marketplace..." style="padding-left: 40px; width: 350px;" onkeyup="handleLiveSearch(this.value)">
        <div id="searchResults" style="display: none; position: absolute; top: 110%; left: 0; width: 100%; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid var(--border-light); border-radius: 8px; z-index: 1000; max-height: 400px; overflow-y: auto;"></div>
    </div>
    
    <div class="header-actions" style="display: flex; align-items: center; gap: 20px;">
        
        <a href="messages.php" class="icon" title="Messages" style="position: relative; display: flex; align-items: center; color: var(--text-muted); text-decoration: none; font-size: 20px;">
            <i class="fa-regular fa-envelope <?php echo ($total_msg_badge > 0) ? 'fa-shake' : ''; ?>"></i>  
            <?php if($total_msg_badge > 0): ?><span class="badge-count"><?php echo $total_msg_badge; ?></span><?php endif; ?>
        </a>
        
        <a href="notifications.php" class="icon" title="Notifications" style="position: relative; display: flex; align-items: center; color: var(--text-muted); text-decoration: none; font-size: 20px;">
            <i class="fa-regular fa-bell <?php echo ($unread_notifs > 0) ? 'fa-shake' : ''; ?>"></i>
            <?php if($unread_notifs > 0): ?><span class="badge-count" id="notifBadge"><?php echo $unread_notifs; ?></span><?php endif; ?>
        </a>
        
        <!-- User Profile Avatar -->
        <div class="user-avatar" style="display: flex; align-items: center;">
            <a href="profile.php" title="My Profile" style="text-decoration:none;">
                <div style="width: 38px; height: 38px; background: linear-gradient(135deg, var(--uiu-orange) 0%, #d95316 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 15px; box-shadow: 0 4px 6px rgba(242, 101, 34, 0.2); overflow: hidden;">
                    <?php if($header_profile_pic): ?>
                        <img src="<?php echo htmlspecialchars($header_profile_pic); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <?php echo $header_initial; ?>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    </div>
</header>

<div class="layout-wrapper">