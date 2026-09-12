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
                        <svg width="22" height="22" fill="#10B981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <?php endif; ?>
                </h1>
                <?php if($profile['bio']): ?>
                    <p class="profile-bio"><?php echo htmlspecialchars($profile['bio']); ?></p>
                <?php endif; ?>
            </div>

            <div class="action-row">
                <?php if($is_own_profile): ?>
                    <button class="btn-primary" style="background: var(--bg-light); color: var(--text-main); border: 1px solid var(--border-light);" onclick="document.getElementById('editProfileModal').style.display='flex'">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Edit Profile
                    </button>
                <?php else: ?>
                    <button class="<?php echo ($connection_status === 'Connected') ? 'btn-outline' : 'btn-primary'; ?>" id="connBtn" onclick="toggleConnection(<?php echo $profile_id; ?>)">
                        <?php if($connection_status === 'Connect'): ?>
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg> Connect
                        <?php elseif($connection_status === 'Pending'): ?>
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Request Sent
                        <?php elseif($connection_status === 'Accept Request'): ?>
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Accept Request
                        <?php else: ?>
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Connected
                        <?php endif; ?>
                    </button>
                    
                    <?php if($connection_status === 'Connected'): ?>
                        <a href="messages.php?user=<?php echo $profile_id; ?>" class="btn-primary" style="text-decoration:none;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg> Message
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
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                    <span>Studies <strong><?php echo htmlspecialchars($profile['department']); ?></strong> at UIU</span>
                </div>
                <div class="intro-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span>Role: <strong><?php echo htmlspecialchars($profile['role']); ?></strong></span>
                </div>
                
                <?php if($job_data): ?>
                <div class="intro-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13V6a2 2 0 00-2-2H5a2 2 0 00-2 2v7m18 0v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5m18 0h-2M5 13H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>Works at <strong><?php echo htmlspecialchars($job_data['current_company']); ?></strong></span>
                </div>
                <?php endif; ?>

                <?php if($profile['current_city']): ?>
                <div class="intro-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Lives in <strong><?php echo htmlspecialchars($profile['current_city']); ?></strong></span>
                </div>
                <?php endif; ?>
                
                <?php if($profile['hometown']): ?>
                <div class="intro-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>From <strong><?php echo htmlspecialchars($profile['hometown']); ?></strong></span>
                </div>
                <?php endif; ?>

                <div class="intro-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
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
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 15px auto; opacity:0.4; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z"></path></svg>
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
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">
            
            <div style="display: flex; gap: 15px; margin-bottom: 15px; align-items: center;">
                <div style="width: 80px; height: 80px; background: var(--bg-light); border-radius: 50%; border: 1px solid var(--border-light); overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?php if($profile['profile_pic']): ?>
                        <img src="<?php echo htmlspecialchars($profile['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
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
                btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Request Sent';
            } else {
                btn.className = 'btn-primary';
                btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg> Connect';
            }
        }
    });
}
</script>

<script src="assets/main.js"></script>
</body>
</html>