<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $user_id;
$is_own_profile = ($profile_id === $user_id);
$current_page = 'profile.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    if ($action == 'report_post') {
        $post_id = intval($_POST['post_id']);
        $conn->query("INSERT IGNORE INTO post_reports (post_id, user_id) VALUES ($post_id, $user_id)");
        echo json_encode(['status' => 'success']); exit();
    }

    if ($action == 'toggle_connection') {
        $target_id = intval($_POST['target_id']);
        $check = $conn->query("SELECT id, status, sender_id FROM connections WHERE (sender_id = $user_id AND receiver_id = $target_id) OR (sender_id = $target_id AND receiver_id = $user_id)");
        
        if ($check->num_rows > 0) {
            $row = $check->fetch_assoc();
            if ($row['status'] == 'pending' && $row['sender_id'] == $target_id) {
                // If receiving a request, accept it
                $conn->query("UPDATE connections SET status = 'accepted' WHERE id = " . $row['id']);
                echo json_encode(['status' => 'success', 'btn_state' => 'Connected']);
            } else {
                // Unfriend or Cancel request
                $conn->query("DELETE FROM connections WHERE id = " . $row['id']);
                echo json_encode(['status' => 'success', 'btn_state' => 'Connect']);
            }
        } else {
            // Send request
            $conn->query("INSERT INTO connections (sender_id, receiver_id, status) VALUES ($user_id, $target_id, 'pending')");
            $conn->query("INSERT INTO notifications (user_id, sender_id, type) VALUES ($target_id, $user_id, 'connection')");
            echo json_encode(['status' => 'success', 'btn_state' => 'Pending']);
        }
        exit();
    }

    if ($action == 'edit_post') {
        $post_id = intval($_POST['post_id']);
        $new_content = $conn->real_escape_string($_POST['content']);
        $new_privacy = $conn->real_escape_string($_POST['privacy'] ?? 'Public');
        $old = $conn->query("SELECT content FROM posts WHERE id = $post_id AND user_id = $user_id")->fetch_assoc();
        if($old){
            $old_content = $conn->real_escape_string($old['content']);
            $conn->query("INSERT INTO post_edit_history (post_id, old_content) VALUES ($post_id, '$old_content')");
            $conn->query("UPDATE posts SET content = '$new_content', privacy = '$new_privacy', is_edited = 1 WHERE id = $post_id");
            echo json_encode(['status' => 'success', 'new_content' => htmlspecialchars($_POST['content'])]);
        } exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile']) && $is_own_profile) {
    $bio = $conn->real_escape_string($_POST['bio']);
    $about = $conn->real_escape_string($_POST['about_me']);
    $city = $conn->real_escape_string($_POST['current_city']);
    $hometown = $conn->real_escape_string($_POST['hometown']);
    
    $pic_query = "";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/profiles/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . '_' . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $pic_query = ", profile_pic = '$target_file'";
        }
    }

    $conn->query("UPDATE users SET bio = '$bio', about_me = '$about', current_city = '$city', hometown = '$hometown' $pic_query WHERE id = $user_id");
    header("Location: profile.php"); exit();
}

$prof_sql = "SELECT * FROM users WHERE id = $profile_id";
$prof_res = $conn->query($prof_sql);
if ($prof_res->num_rows == 0) { echo "User not found."; exit(); }
$profile = $prof_res->fetch_assoc();

$prof_initials = strtoupper($profile['full_name'][0] . (isset(explode(" ", $profile['full_name'])[1]) ? explode(" ", $profile['full_name'])[1][0] : ''));

$skills = [];
$s_res = $conn->query("SELECT skill_name FROM user_skills WHERE user_id = $profile_id");
while($s = $s_res->fetch_assoc()) { $skills[] = $s['skill_name']; }

$job_data = null;
$j_res = $conn->query("SELECT designation, current_company FROM alumni_profiles WHERE user_id = $profile_id");
if($j_res->num_rows > 0) $job_data = $j_res->fetch_assoc();

$connection_status = 'Connect';
$can_see_posts = $is_own_profile;

if (!$is_own_profile) {
    $conn_check = $conn->query("SELECT sender_id, status FROM connections WHERE (sender_id = $user_id AND receiver_id = $profile_id) OR (sender_id = $profile_id AND receiver_id = $user_id)");
    if ($conn_check->num_rows > 0) {
        $c_data = $conn_check->fetch_assoc();
        if ($c_data['status'] === 'accepted') {
            $connection_status = 'Connected';
            $can_see_posts = true; 
        } else {
            $connection_status = ($c_data['sender_id'] == $user_id) ? 'Pending' : 'Accept Request';
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/profile.css">
<style>
    .profile-nav-tabs { display: flex; gap: 20px; border-bottom: 1px solid var(--border-light); margin-bottom: 20px; }
    .profile-nav-tabs button { background: none; border: none; padding: 10px 5px; font-size: 15px; font-weight: 600; color: var(--text-muted); cursor: pointer; border-bottom: 2px solid transparent; transition: 0.2s; }
    .profile-nav-tabs button.active { color: var(--uiu-orange); border-bottom-color: var(--uiu-orange); }
    .profile-tab-content { display: none; }
    .profile-tab-content.active { display: block; }
</style>

<main class="profile-layout">
    
    <div class="profile-header-card">
        <div class="cover-photo"></div>
        <div class="profile-meta-area">
            <div class="profile-avatar-large">
                <?php if($profile['profile_pic']): ?>
                    <img src="<?php echo htmlspecialchars($profile['profile_pic']); ?>" alt="DP">
                <?php else: ?>
                    <?php echo $prof_initials; ?>
                <?php endif; ?>
            </div>
            
            <div class="profile-info">
                <h1 class="profile-name">
                    <?php echo htmlspecialchars($profile['full_name']); ?>
                    <?php if($profile['role'] === 'Faculty'): ?>
                        <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 20px;"></i>
                    <?php endif; ?>
                </h1>
                <?php if($profile['bio']): ?>
                    <p class="profile-bio"><?php echo htmlspecialchars($profile['bio']); ?></p>
                <?php endif; ?>
            </div>

            <div class="action-row">
                <?php if($is_own_profile): ?>
                    <button class="btn-primary" style="background: var(--bg-light); color: var(--text-main); border: 1px solid var(--border-light);" onclick="document.getElementById('editProfileModal').style.display='flex'">
                        <i class="fa-solid fa-pen-to-square" style="margin-right:5px; vertical-align:middle; font-size: 16px;"></i> Edit Profile
                    </button>
                <?php else: ?>
                    <button class="<?php echo ($connection_status === 'Connected') ? 'btn-outline' : 'btn-primary'; ?>" id="connBtn" onclick="toggleConnection(<?php echo $profile_id; ?>)">
                        <?php if($connection_status === 'Connect'): ?>
                            <i class="fa-solid fa-user-plus" style="margin-right:5px; vertical-align:middle; font-size: 16px;"></i> Connect
                        <?php elseif($connection_status === 'Pending'): ?>
                            <i class="fa-solid fa-clock-rotate-left" style="margin-right:5px; vertical-align:middle; font-size: 16px;"></i> Request Sent
                        <?php elseif($connection_status === 'Accept Request'): ?>
                            <i class="fa-solid fa-check" style="margin-right:5px; vertical-align:middle; font-size: 16px;"></i> Accept Request
                        <?php else: ?>
                            <i class="fa-solid fa-user-check" style="margin-right:5px; vertical-align:middle; font-size: 16px;"></i> Connected
                        <?php endif; ?>
                    </button>
                    
                    <?php if($connection_status === 'Connected'): ?>
                        <a href="messages.php?user=<?php echo $profile_id; ?>" class="btn-primary" style="text-decoration:none;">
                            <i class="fa-regular fa-message" style="margin-right:5px; vertical-align:middle; font-size: 16px;"></i> Message
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="profile-body">
        <div class="intro-column">
            <div class="intro-card">
                <h3 class="intro-title">Intro</h3>
                <div class="intro-item">
                    <i class="fa-solid fa-graduation-cap" style="color: var(--text-muted); width: 20px; font-size: 16px; text-align: center;"></i>
                    <span>Studies <strong><?php echo htmlspecialchars($profile['department']); ?></strong> at UIU</span>
                </div>
                <div class="intro-item">
                    <i class="fa-solid fa-briefcase" style="color: var(--text-muted); width: 20px; font-size: 16px; text-align: center;"></i>
                    <span>Role: <strong><?php echo htmlspecialchars($profile['role']); ?></strong></span>
                </div>
                
                <?php if($job_data): ?>
                <div class="intro-item">
                    <i class="fa-regular fa-building" style="color: var(--text-muted); width: 20px; font-size: 16px; text-align: center;"></i>
                    <span>Works at <strong><?php echo htmlspecialchars($job_data['current_company']); ?></strong></span>
                </div>
                <?php endif; ?>

                <?php if($profile['current_city']): ?>
                <div class="intro-item">
                    <i class="fa-solid fa-house" style="color: var(--text-muted); width: 20px; font-size: 16px; text-align: center;"></i>
                    <span>Lives in <strong><?php echo htmlspecialchars($profile['current_city']); ?></strong></span>
                </div>
                <?php endif; ?>
                
                <?php if($profile['hometown']): ?>
                <div class="intro-item">
                    <i class="fa-solid fa-location-dot" style="color: var(--text-muted); width: 20px; font-size: 16px; text-align: center;"></i>
                    <span>From <strong><?php echo htmlspecialchars($profile['hometown']); ?></strong></span>
                </div>
                <?php endif; ?>

                <div class="intro-item">
                    <i class="fa-regular fa-calendar-days" style="color: var(--text-muted); width: 20px; font-size: 16px; text-align: center;"></i>
                    <span>Joined <?php echo date('F Y', strtotime($profile['created_at'])); ?></span>
                </div>

                <?php if($is_own_profile): ?>
                    <button class="btn-outline" style="width: 100%; margin-top: 10px; background: var(--bg-light); border-color: var(--border-light);" onclick="document.getElementById('editProfileModal').style.display='flex'">Edit details</button>
                <?php endif; ?>
            </div>

            <div class="intro-card">
                <h3 class="intro-title" style="display: flex; justify-content: space-between; align-items: center;">About Me</h3>
                <?php if($profile['about_me']): ?>
                    <p style="font-size: 14px; color: var(--text-main); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($profile['about_me'])); ?></p>
                <?php else: ?>
                    <p style="font-size: 14px; color: var(--text-muted);">No details added yet.</p>
                <?php endif; ?>
            </div>

            <div class="intro-card">
                <h3 class="intro-title" style="display: flex; justify-content: space-between; align-items: center;">
                    Skills
                    <?php if($is_own_profile): ?>
                        <a href="skills.php" style="font-size: 13px; color: var(--uiu-orange); text-decoration: none; font-weight: 500;">Add Skills</a>
                    <?php endif; ?>
                </h3>
                <?php if(count($skills) > 0): ?>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <?php foreach($skills as $s): ?>
                            <span class="skill-badge"><?php echo htmlspecialchars($s); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size: 13px; color: var(--text-muted);">No skills added.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="posts-column">
            <?php if($can_see_posts): ?>
                
                <div class="profile-nav-tabs">
                    <button class="tab-btn active" onclick="switchProfileTab('posts', this)"><i class="fa-regular fa-newspaper" style="margin-right: 6px;"></i> Posts</button>
                    <button class="tab-btn" onclick="switchProfileTab('projects', this)"><i class="fa-solid fa-laptop-code" style="margin-right: 6px;"></i> Projects</button>
                    <button class="tab-btn" onclick="switchProfileTab('connections', this)"><i class="fa-solid fa-user-group" style="margin-right: 6px;"></i> Connections</button>
                </div>

                <!-- TAB: POSTS -->
                <div id="tab-posts" class="profile-tab-content active">
                    <?php
                    $privacy_filter = $is_own_profile ? "" : "AND p.privacy != 'Only Me'";
                    $sql = "SELECT p.*, u.full_name, u.role, u.department FROM posts p JOIN users u ON p.user_id = u.id WHERE p.user_id = $profile_id $privacy_filter ORDER BY p.created_at DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $post_id = $row['id'];
                            ?>
                            <div class="post-card" style="background:#fff; border:1px solid var(--border-light); border-radius:12px; margin-bottom:20px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                                <div class="post-header" style="display:flex; justify-content:space-between; align-items:center;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                        <div class="avatar-sm" style="background: var(--bg-light); border: 1px solid var(--border-light); color: var(--text-main); width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">
                                            <?php if($profile['profile_pic']): ?>
                                                <img src="<?php echo htmlspecialchars($profile['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                            <?php else: ?>
                                                <?php echo $prof_initials; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div style="line-height: 1.2;">
                                            <strong style="font-size: 15px; color: var(--text-main);"><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                            <small style="color: var(--text-muted); font-size: 13px;">
                                                <?php echo date('F d \a\t g:i A', strtotime($row['created_at'])); ?>
                                                <i class="fa-solid <?php echo $row['privacy'] === 'Public' ? 'fa-globe' : ($row['privacy'] === 'Connections' ? 'fa-user-group' : 'fa-lock'); ?>" style="margin-left: 5px; font-size: 10px;" title="<?php echo $row['privacy']; ?>"></i>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div style="position: relative;">
                                        <button style="background:none; border:none; color:var(--text-muted); cursor:pointer;" onclick="toggleMenu('post-menu-<?php echo $post_id; ?>')">
                                            <i class="fa-solid fa-ellipsis" style="font-size: 24px;"></i>
                                        </button>
                                        <div id="post-menu-<?php echo $post_id; ?>" class="post-options-menu" onmouseleave="this.style.display='none'">
                                            <?php if($row['user_id'] == $user_id): ?>
                                                <button onclick="openEditPost(<?php echo $post_id; ?>, '<?php echo $row['privacy']; ?>')"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                                <button style="color:#EF4444;" onclick="openDeleteModal(<?php echo $post_id; ?>)"><i class="fa-solid fa-trash"></i> Delete</button>
                                            <?php else: ?>
                                                <button style="color:#F59E0B;" onclick="reportPost(<?php echo $post_id; ?>)"><i class="fa-solid fa-flag"></i> Report</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <p id="post-content-<?php echo $post_id; ?>" class="post-content-text" data-raw="<?php echo htmlspecialchars($row['content']); ?>"><?php echo htmlspecialchars($row['content']); ?></p>
                                <?php if($row['media_path']): ?>
                                    <img src="<?php echo $row['media_path']; ?>" style="width:100%; border-radius:8px; border:1px solid var(--border-light);">
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="private-state"><i class="fa-regular fa-newspaper" style="font-size: 48px; opacity: 0.3; margin-bottom: 10px; display:block;"></i> No posts published yet.</div>';
                    }
                    ?>
                </div>

                <!-- TAB: PROJECTS -->
                <div id="tab-projects" class="profile-tab-content">
                    <?php
                    $proj_sql = "SELECT * FROM projects WHERE user_id = $profile_id ORDER BY created_at DESC";
                    $proj_res = $conn->query($proj_sql);

                    if ($proj_res && $proj_res->num_rows > 0) {
                        while ($p = $proj_res->fetch_assoc()) {
                            ?>
                            <div style="background:#fff; border:1px solid var(--border-light); border-radius:12px; margin-bottom:20px; overflow:hidden;">
                                <?php if($p['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($p['image_path']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 120px; background: var(--bg-light); display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-laptop-code" style="font-size: 32px; color: var(--text-muted); opacity: 0.3;"></i>
                                    </div>
                                <?php endif; ?>
                                <div style="padding: 16px;">
                                    <h3 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;"><?php echo htmlspecialchars($p['title']); ?></h3>
                                    <?php if($p['tech_stack']): 
                                        $techs = explode(',', $p['tech_stack']);
                                        foreach($techs as $tech): ?>
                                            <span style="display:inline-block; font-size:11px; font-weight:600; color:var(--text-main); background:var(--bg-light); padding:2px 8px; border-radius:12px; margin-bottom:10px; border:1px solid var(--border-light);"><?php echo htmlspecialchars(trim($tech)); ?></span>
                                    <?php endforeach; endif; ?>
                                    <p style="font-size: 14px; color: var(--text-muted); line-height: 1.5; margin-bottom: 15px;"><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
                                    <div style="display: flex; gap: 10px;">
                                        <?php if(!empty($p['repo_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($p['repo_url']); ?>" target="_blank" style="font-size:13px; font-weight:600; color:var(--text-main); text-decoration:none;"><i class="fa-brands fa-github"></i> Repository</a>
                                        <?php endif; ?>
                                        <?php if(!empty($p['live_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($p['live_url']); ?>" target="_blank" style="font-size:13px; font-weight:600; color:var(--uiu-orange); text-decoration:none; margin-left:15px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Live Demo</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="private-state"><i class="fa-solid fa-laptop-code" style="font-size: 48px; opacity: 0.3; margin-bottom: 10px; display:block;"></i> No projects showcased yet.</div>';
                    }
                    ?>
                </div>

                <!-- TAB: CONNECTIONS -->
                <div id="tab-connections" class="profile-tab-content">
                    <?php if($is_own_profile): ?>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <?php
                            $conn_sql = "SELECT u.id, u.full_name, u.profile_pic, u.department, u.role FROM connections c JOIN users u ON (c.sender_id = u.id OR c.receiver_id = u.id) WHERE (c.sender_id = $profile_id OR c.receiver_id = $profile_id) AND u.id != $profile_id AND c.status = 'accepted'";
                            $conn_res = $conn->query($conn_sql);

                            if ($conn_res && $conn_res->num_rows > 0) {
                                while ($friend = $conn_res->fetch_assoc()) {
                                    $f_initial = strtoupper($friend['full_name'][0]);
                                    ?>
                                    <a href="profile.php?id=<?php echo $friend['id']; ?>" style="display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; background: #fff; text-decoration: none; transition: 0.2s;" onmouseover="this.style.borderColor='var(--uiu-orange)'" onmouseout="this.style.borderColor='var(--border-light)'">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--bg-light); border: 1px solid var(--border-light); display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--text-main); overflow: hidden;">
                                            <?php if($friend['profile_pic']): ?>
                                                <img src="<?php echo htmlspecialchars($friend['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                            <?php else: echo $f_initial; endif; ?>
                                        </div>
                                        <div style="overflow: hidden;">
                                            <strong style="font-size: 14px; color: var(--text-main); display: block; white-space: nowrap; text-overflow: ellipsis;"><?php echo htmlspecialchars($friend['full_name']); ?></strong>
                                            <span style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($friend['department']); ?></span>
                                        </div>
                                    </a>
                                    <?php
                                }
                            } else {
                                echo '<div class="private-state" style="grid-column: 1 / -1;"><i class="fa-solid fa-user-group" style="font-size: 48px; opacity: 0.3; margin-bottom: 10px; display:block;"></i> No connections found.</div>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="private-state"><i class="fa-solid fa-user-lock" style="font-size: 48px; opacity: 0.3; margin-bottom: 10px; display:block;"></i> Connections are private.</div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="private-state">
                    <i class="fa-solid fa-lock" style="font-size: 48px; margin: 0 auto 15px auto; opacity:0.4; display:block;"></i>
                    <h3 style="font-size:20px; color:var(--text-main); margin-bottom:8px;">This profile is private</h3>
                    <p style="font-size:14px;">Connect with <?php echo explode(" ", $profile['full_name'])[0]; ?> to see their timeline, projects, and network.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php if($is_own_profile): ?>
<div class="modal-overlay" id="editProfileModal">
    <div class="modal-content" style="max-width: 550px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Edit Profile</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('editProfileModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 20px;"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">
            
            <div style="display: flex; gap: 15px; margin-bottom: 15px; align-items: center;">
                <div style="width: 80px; height: 80px; background: var(--bg-light); border-radius: 50%; border: 1px solid var(--border-light); overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?php if($profile['profile_pic']): ?>
                        <img src="<?php echo htmlspecialchars($profile['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <i class="fa-solid fa-user" style="color:var(--text-muted); font-size: 32px;"></i>
                    <?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Update Profile Picture</label>
                    <input type="file" name="profile_pic" class="poll-input" style="padding: 6px 10px;" accept="image/*">
                </div>
            </div>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Short Bio</label>
            <input type="text" name="bio" class="poll-input" value="<?php echo htmlspecialchars($profile['bio']); ?>" placeholder="e.g. Code enthusiast | Competitive Programmer" maxlength="100">
            
            <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Current City</label>
                    <input type="text" name="current_city" class="poll-input" value="<?php echo htmlspecialchars($profile['current_city']); ?>" placeholder="e.g. Dhaka">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Hometown</label>
                    <input type="text" name="hometown" class="poll-input" value="<?php echo htmlspecialchars($profile['hometown']); ?>" placeholder="e.g. Sylhet">
                </div>
            </div>

            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">About Me</label>
            <textarea name="about_me" class="poll-input" rows="4" placeholder="Write a few sentences about yourself..."><?php echo htmlspecialchars($profile['about_me']); ?></textarea>

            <button type="submit" class="btn-primary" style="width:100%; margin-top: 10px; padding: 12px; font-size: 14px;">Save Changes</button>
        </form>
    </div>
</div>

<!-- EDIT POST MODAL FOR PROFILE -->
<div class="modal-overlay" id="editModal">
    <div class="modal-content" style="max-width: 500px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Edit Post</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('editModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 24px;"></i>
            </button>
        </div>
        <div style="margin-bottom: 15px;">
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Post Privacy</label>
            <select id="editPrivacy" class="poll-input" style="padding: 8px; font-size: 13px;">
                <option value="Public">Public</option>
                <option value="Connections">Connections</option>
                <option value="Only Me">Only Me</option>
            </select>
        </div>
        <textarea id="editText" class="composer-input" style="width:100%; min-height: 100px; padding:0; margin-bottom:15px; border: 1px solid var(--border-light); padding: 10px; border-radius: 8px;"></textarea>
        <input type="hidden" id="editPostId">
        <button class="btn-primary" style="width:100%; justify-content: center;" onclick="submitEdit()">Save Changes</button>
    </div>
</div>
<?php endif; ?>

<script>
function reportPost(postId) {
    if(confirm("Report this post to admins?")) {
        let fd = new FormData();
        fd.append('ajax_action', 'report_post');
        fd.append('post_id', postId);
        fetch('profile.php', { method: 'POST', body: fd })
        .then(r=>r.json()).then(data=>{
            if(data.status === 'success') showToast("Post reported to admins");
        });
    }
}

function openEditPost(postId, currentPrivacy = 'Public') {
    document.getElementById('editPostId').value = postId;
    let content = document.getElementById('post-content-' + postId).getAttribute('data-raw');
    document.getElementById('editText').value = content;
    
    let privSelect = document.getElementById('editPrivacy');
    if(privSelect) privSelect.value = currentPrivacy;

    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('post-menu-' + postId).style.display = 'none';
}

function submitEdit() {
    let postId = document.getElementById('editPostId').value;
    let content = document.getElementById('editText').value.trim();
    let privacy = document.getElementById('editPrivacy') ? document.getElementById('editPrivacy').value : 'Public';
    if(content === '') return;

    let fd = new FormData();
    fd.append('ajax_action', 'edit_post');
    fd.append('post_id', postId);
    fd.append('content', content);
    fd.append('privacy', privacy);

    fetch(window.location.href, { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
        if(data.status === 'success') {
            document.getElementById('editModal').style.display = 'none';
            showToast("Post updated successfully");
            setTimeout(() => location.reload(), 800); 
        }
    });
}

function toggleConnection(targetId) {
    let btn = document.getElementById('connBtn');
    let fd = new FormData();
    fd.append('ajax_action', 'toggle_connection');
    fd.append('target_id', targetId);
    
    fetch('profile.php?id=' + targetId, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            if(data.btn_state === 'Pending') {
                btn.className = 'btn-primary';
                btn.innerHTML = '<i class="fa-solid fa-clock-rotate-left" style="margin-right:5px; vertical-align:middle; font-size:16px;"></i> Request Sent';
            } else if(data.btn_state === 'Connected') {
                btn.className = 'btn-outline';
                btn.innerHTML = '<i class="fa-solid fa-user-check" style="margin-right:5px; vertical-align:middle; font-size:16px;"></i> Connected';
                setTimeout(() => location.reload(), 800); 
            } else {
                btn.className = 'btn-primary';
                btn.innerHTML = '<i class="fa-solid fa-user-plus" style="margin-right:5px; vertical-align:middle; font-size:16px;"></i> Connect';
                setTimeout(() => location.reload(), 800); 
            }
        }
    });
}

function switchProfileTab(tabName, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.profile-tab-content').forEach(c => c.classList.remove('active'));
    
    btn.classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}
</script>

<script src="assets/main.js"></script>
</body>
</html>