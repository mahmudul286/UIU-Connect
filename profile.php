<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $user_id;
$is_own_profile = ($profile_id === $user_id);
$current_page = 'profile.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    if ($_POST['ajax_action'] == 'toggle_connection') {
        $target_id = intval($_POST['target_id']);
        $check = $conn->query("SELECT id, status FROM connections WHERE (sender_id = $user_id AND receiver_id = $target_id) OR (sender_id = $target_id AND receiver_id = $user_id)");
        
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM connections WHERE (sender_id = $user_id AND receiver_id = $target_id) OR (sender_id = $target_id AND receiver_id = $user_id)");
            echo json_encode(['status' => 'success', 'btn_state' => 'Connect']);
        } else {
            $conn->query("INSERT INTO connections (sender_id, receiver_id, status) VALUES ($user_id, $target_id, 'pending')");
            echo json_encode(['status' => 'success', 'btn_state' => 'Pending']);
        }
        exit();
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
                <h3 class="intro-title" style="display: flex; justify-content: space-between; align-items: center;">
                    About Me
                </h3>
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
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 20px; font-weight: 700; color: var(--text-main);">Posts</h3>
                </div>

                <div id="postsArea">
                    <?php
                    $sql = "SELECT p.*, u.full_name, u.role, u.department FROM posts p JOIN users u ON p.user_id = u.id WHERE p.user_id = $profile_id ORDER BY p.created_at DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <div class="post-card" style="background:#fff; border:1px solid var(--border-light); border-radius:12px; margin-bottom:20px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
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
                                        <small style="color: var(--text-muted); font-size: 13px;"><?php echo date('F d \a\t g:i A', strtotime($row['created_at'])); ?></small>
                                    </div>
                                </div>
                                <p style="font-size: 15px; color: var(--text-main); margin-bottom: 15px; line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($row['content']); ?></p>
                                <?php if($row['media_path']): ?>
                                    <img src="<?php echo $row['media_path']; ?>" style="width:100%; border-radius:8px; border:1px solid var(--border-light);">
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="private-state">No posts published yet.</div>';
                    }
                    ?>
                </div>
            <?php else: ?>
                <div class="private-state">
                    <i class="fa-solid fa-lock" style="font-size: 48px; margin: 0 auto 15px auto; opacity:0.4; display:block;"></i>
                    <h3 style="font-size:20px; color:var(--text-main); margin-bottom:8px;">This profile is private</h3>
                    <p style="font-size:14px;">Connect with <?php echo explode(" ", $profile['full_name'])[0]; ?> to see their timeline.</p>
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
<?php endif; ?>

<script>
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
            } else {
                btn.className = 'btn-primary';
                btn.innerHTML = '<i class="fa-solid fa-user-plus" style="margin-right:5px; vertical-align:middle; font-size:16px;"></i> Connect';
            }
        }
    });
}
</script>

<script src="assets/main.js"></script>
</body>
</html>