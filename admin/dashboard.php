<?php
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$admin_res = $conn->query("SELECT full_name, email FROM users WHERE id = $user_id");
if ($admin_res && $admin_res->num_rows > 0) {
    $admin_data = $admin_res->fetch_assoc();
    $admin_name = $admin_data['full_name'];
    $admin_email = $admin_data['email'] ?? '';
} else {
    $admin_name = "Admin";
    $admin_email = "";
}
$admin_initial = strtoupper($admin_name[0] ?? 'A');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    // --- Admin Settings Actions ---
    if ($action == 'update_admin_profile') {
        $name = $conn->real_escape_string(trim($_POST['name']));
        $email = $conn->real_escape_string(trim($_POST['email']));
        
        if(!empty($name) && !empty($email)) {
            $check_email = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $user_id");
            if ($check_email && $check_email->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'This email is already in use by another user!']);
                exit();
            }

            $update = $conn->query("UPDATE users SET full_name = '$name', email = '$email' WHERE id = $user_id");
            if ($update) {
                $_SESSION['full_name'] = $name; 
                echo json_encode(['status' => 'success', 'message' => 'Profile information updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error: Could not update profile.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Name and Email cannot be empty.']);
        }
        exit();
    }

    if ($action == 'update_admin_password') {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        
        $user_data = $conn->query("SELECT password_hash FROM users WHERE id = $user_id")->fetch_assoc();
        $user_hash = $user_data['password_hash'] ?? '';
        
        if (password_verify($old_pass, $user_hash)) {
            if(strlen($new_pass) >= 6) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password_hash = '$new_hash' WHERE id = $user_id");
                echo json_encode(['status' => 'success', 'message' => 'Password changed successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password!']);
        }
        exit();
    }

    // --- Course Assignment Action (NEW) ---
    if ($action == 'assign_faculty') {
        $course_id = intval($_POST['course_id']);
        $faculty_id = intval($_POST['faculty_id']);
        
        if($faculty_id == 0) {
            $conn->query("UPDATE courses SET faculty_id = NULL WHERE id = $course_id");
            echo json_encode(['status' => 'success', 'message' => 'Faculty unassigned from course.']);
        } else {
            $conn->query("UPDATE courses SET faculty_id = $faculty_id WHERE id = $course_id");
            echo json_encode(['status' => 'success', 'message' => 'Course successfully assigned to Faculty.']);
        }
        exit();
    }

    if ($action == 'approve_item') {
        $item_id = intval($_POST['item_id']);
        $conn->query("UPDATE marketplace SET admin_approval_status = 'approved' WHERE id = $item_id");
        $seller = $conn->query("SELECT seller_id FROM marketplace WHERE id = $item_id")->fetch_assoc();
        if ($seller) {
            $conn->query("INSERT INTO notifications (user_id, sender_id, type) VALUES (".$seller['seller_id'].", $user_id, 'announcement')");
        }
        echo json_encode(['status' => 'success', 'message' => 'Item approved.']); exit();
    }

    if ($action == 'reject_item') {
        $item_id = intval($_POST['item_id']);
        $check = $conn->query("SELECT image_path FROM marketplace WHERE id = $item_id");
        if ($check->num_rows > 0) {
            $img = $check->fetch_assoc()['image_path'];
            if ($img && file_exists('../' . $img)) unlink('../' . $img); 
            $conn->query("DELETE FROM marketplace WHERE id = $item_id");
        }
        echo json_encode(['status' => 'success', 'message' => 'Item rejected and deleted.']); exit();
    }

    if ($action == 'approve_job') {
        $job_id = intval($_POST['job_id']);
        $conn->query("UPDATE jobs SET admin_approval_status = 'approved' WHERE id = $job_id");
        $poster = $conn->query("SELECT posted_by FROM jobs WHERE id = $job_id")->fetch_assoc();
        if ($poster) {
            $conn->query("INSERT INTO notifications (user_id, sender_id, type) VALUES (".$poster['posted_by'].", $user_id, 'announcement')");
        }
        echo json_encode(['status' => 'success', 'message' => 'Job posting approved.']); exit();
    }

    if ($action == 'reject_job') {
        $job_id = intval($_POST['job_id']);
        $conn->query("DELETE FROM jobs WHERE id = $job_id");
        echo json_encode(['status' => 'success', 'message' => 'Job posting rejected.']); exit();
    }

    if ($action == 'create_club') {
        $name = $conn->real_escape_string($_POST['club_name']);
        $cat = $conn->real_escape_string($_POST['club_category']);
        $desc = $conn->real_escape_string($_POST['club_desc']);
        $conn->query("INSERT INTO clubs (name, category, description) VALUES ('$name', '$cat', '$desc')");
        echo json_encode(['status' => 'success', 'message' => 'New club created successfully.']); exit();
    }

    if ($action == 'delete_club') {
        $club_id = intval($_POST['club_id']);
        $conn->query("DELETE FROM clubs WHERE id = $club_id");
        echo json_encode(['status' => 'success', 'message' => 'Club deleted.']); exit();
    }

    if ($action == 'broadcast_announcement') {
        $content = $conn->real_escape_string($_POST['content']);
        $conn->query("INSERT INTO posts (user_id, post_type, content, privacy) VALUES ($user_id, 'Announcement', '$content', 'Public')");
        $post_id = $conn->insert_id;
        
        $users = $conn->query("SELECT id FROM users WHERE id != $user_id");
        while($u = $users->fetch_assoc()) {
            $conn->query("INSERT INTO notifications (user_id, sender_id, type, reference_id) VALUES (".$u['id'].", $user_id, 'announcement', $post_id)");
        }
        echo json_encode(['status' => 'success', 'message' => 'Announcement broadcasted campus-wide.']); exit();
    }

    if ($action == 'delete_announcement') {
        $post_id = intval($_POST['post_id']);
        $conn->query("DELETE FROM posts WHERE id = $post_id AND post_type = 'Announcement'");
        echo json_encode(['status' => 'success', 'message' => 'Announcement deleted.']); exit();
    }

    if ($action == 'delete_blood_request') {
        $req_id = intval($_POST['req_id']);
        $conn->query("DELETE FROM blood_requests WHERE id = $req_id");
        echo json_encode(['status' => 'success', 'message' => 'Blood request removed due to spam/violation.']); exit();
    }

    if ($action == 'delete_reported_post') {
        $post_id = intval($_POST['post_id']);
        $check = $conn->query("SELECT media_path FROM posts WHERE id = $post_id");
        if ($check->num_rows > 0) {
            $media = $check->fetch_assoc()['media_path'];
            if ($media && file_exists('../' . $media)) unlink('../' . $media); 
            $conn->query("DELETE FROM posts WHERE id = $post_id");
        }
        echo json_encode(['status' => 'success', 'message' => 'Post deleted successfully.']); exit();
    }

    if ($action == 'dismiss_report') {
        $post_id = intval($_POST['post_id']);
        $conn->query("DELETE FROM post_reports WHERE post_id = $post_id");
        echo json_encode(['status' => 'success', 'message' => 'Reports dismissed. Post kept.']); exit();
    }

    if ($action == 'delete_user') {
        $target_user = intval($_POST['target_user']);
        if ($target_user !== $user_id) { 
            $conn->query("DELETE FROM users WHERE id = $target_user");
        }
        echo json_encode(['status' => 'success']); exit();
    }

    if ($action == 'change_role') {
        $target_user = intval($_POST['target_user']);
        $new_role = $conn->real_escape_string($_POST['new_role']);
        if ($target_user !== $user_id && in_array($new_role, ['Student', 'Faculty', 'Admin'])) {
            $conn->query("UPDATE users SET role = '$new_role' WHERE id = $target_user");
        }
        echo json_encode(['status' => 'success']); exit();
    }
}

$total_users = $conn->query("SELECT COUNT(id) as c FROM users")->fetch_assoc()['c'];
$total_posts = $conn->query("SELECT COUNT(id) as c FROM posts")->fetch_assoc()['c'];
$pending_items = $conn->query("SELECT COUNT(id) as c FROM marketplace WHERE admin_approval_status = 'pending'")->fetch_assoc()['c'];
$pending_jobs = $conn->query("SELECT COUNT(id) as c FROM jobs WHERE admin_approval_status = 'pending'")->fetch_assoc()['c'];
$total_reports = $conn->query("SELECT COUNT(DISTINCT post_id) as c FROM post_reports")->fetch_assoc()['c'];
$active_blood_reqs = $conn->query("SELECT COUNT(id) as c FROM blood_requests WHERE status = 'active'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UIU Connect</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; }
        .admin-content { flex: 1; width: 100%; max-width: 100%; padding-bottom: 50px; }
        .stat-card { background: #fff; padding: 24px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: transform 0.2s ease; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px rgba(0,0,0,0.05); }
        .stat-icon { width: 64px; height: 64px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; }
        .stat-info h3 { font-size: 28px; color: var(--text-main); margin-bottom: 4px; font-weight: 800; }
        .stat-info p { font-size: 13px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin: 0; letter-spacing: 0.5px; }
        
        .admin-section { background: #fff; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05); margin-bottom: 24px; overflow: hidden; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.02); animation: fadeIn 0.3s ease; }
        .admin-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .section-header { padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.05); background: #fff; display: flex; justify-content: space-between; align-items: center; }
        .section-header h3 { font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px 24px; text-align: left; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 14px; }
        th { background: #f8fafc; color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        td { color: var(--text-main); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #f8fafc; }

        .btn-action { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; }
        .btn-accept { background: #10B981; color: white; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2); }
        .btn-accept:hover { background: #059669; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3); }
        .btn-reject { background: #EF4444; color: white; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2); }
        .btn-reject:hover { background: #DC2626; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.3); }
        .btn-view { background: #3B82F6; color: white; text-decoration: none; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2); }
        .btn-view:hover { background: #2563EB; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3); }
        
        .sidebar { background: #fff; padding: 24px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-right: 24px; height: calc(100vh - 120px); position: sticky; top: 100px; }
        .sidebar-menu { padding: 0; margin: 0; list-style: none; }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px; color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 15px; transition: all 0.2s ease; cursor: pointer; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: var(--uiu-orange-light); color: var(--uiu-orange); transform: translateX(5px); }
        .sidebar-menu a i { font-size: 18px; width: 24px; text-align: center; }

        .input-field { width: 100%; padding: 12px 16px; border: 1px solid rgba(0,0,0,0.1); border-radius: 10px; font-size: 14px; margin-top: 8px; box-sizing: border-box; background: #f8fafc; transition: 0.2s; outline: none; }
        .input-field:focus { background: #fff; border-color: var(--uiu-orange); box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1); }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        .settings-card { background: #f8fafc; border: 1px solid rgba(0,0,0,0.05); border-radius: 12px; padding: 24px; }
        .settings-card h4 { margin: 0 0 15px 0; font-size: 15px; color: var(--text-main); display: flex; align-items: center; gap: 8px; }
        .settings-card label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-top: 15px; display: block; }
    </style>
</head>
<body>

<header class="top-header">
    <div class="logo">
        <a href="#" style="font-size: 24px; font-weight: 700; text-decoration:none; color:var(--text-main);">
            <strong>UIU</strong><span style="font-weight: 600; color:black;">Connect</span>
            <span style="background:#EF4444; color:white; font-size:10px; padding:2px 8px; border-radius:6px; vertical-align:middle; margin-left:8px; font-weight:800; letter-spacing:1px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);">ADMIN</span>
        </a>
    </div>
    
    <div class="header-actions" style="display: flex; align-items: center;">
        <div class="user-avatar" style="display: flex; align-items: center; gap: 12px; padding: 6px 12px; background: #f8fafc; border-radius: 30px; border: 1px solid rgba(0,0,0,0.05);">
            <div style="text-align: right;">
                <strong style="font-size: 14px; color: var(--text-main); display: block;"><?php echo htmlspecialchars($admin_name); ?></strong>
                <small style="color: var(--text-muted); font-size: 11px; font-weight: 600; text-transform: uppercase;">System Admin</small>
            </div>
            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                <?php echo $admin_initial; ?>
            </div>
        </div>
    </div>
</header>

<div id="toastMessage" class="toast" style="display: flex; align-items: center; gap: 10px;">
    <i id="toastIcon" class="fa-solid fa-check" style="color: #10B981; font-size: 20px;"></i>
    <span id="toastText">Action successful</span>
</div>

<div class="layout-wrapper" style="max-width: 1400px;">
    <!-- Left Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a onclick="switchTab('overview', this)" class="active"><i class="fa-solid fa-chart-pie"></i> Overview</a></li>
            <li><a onclick="switchTab('courses', this)"><i class="fa-solid fa-book"></i> Assign Courses</a></li>
            <li><a onclick="switchTab('announcements', this)"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
            <li><a onclick="switchTab('users', this)"><i class="fa-solid fa-users"></i> Users</a></li>
            <li><a onclick="switchTab('bloodbank', this)"><i class="fa-solid fa-droplet"></i> Blood Bank <span style="background:#10B981; color:white; padding:2px 6px; border-radius:10px; font-size:10px; margin-left:auto; <?php echo $active_blood_reqs == 0 ? 'display:none;' : ''; ?>"><?php echo $active_blood_reqs; ?></span></a></li>
            <li><a onclick="switchTab('marketplace', this)"><i class="fa-solid fa-store"></i> Marketplace <span style="background:#EF4444; color:white; padding:2px 6px; border-radius:10px; font-size:10px; margin-left:auto; <?php echo $pending_items == 0 ? 'display:none;' : ''; ?>"><?php echo $pending_items; ?></span></a></li>
            <li><a onclick="switchTab('jobs', this)"><i class="fa-solid fa-briefcase"></i> Job Approvals <span style="background:#EF4444; color:white; padding:2px 6px; border-radius:10px; font-size:10px; margin-left:auto; <?php echo $pending_jobs == 0 ? 'display:none;' : ''; ?>"><?php echo $pending_jobs; ?></span></a></li>
            <li><a onclick="switchTab('clubs', this)"><i class="fa-solid fa-people-group"></i> Campus Clubs</a></li>
            <li><a onclick="switchTab('reports', this)"><i class="fa-solid fa-flag"></i> Reported Posts <span style="background:#EF4444; color:white; padding:2px 6px; border-radius:10px; font-size:10px; margin-left:auto; <?php echo $total_reports == 0 ? 'display:none;' : ''; ?>"><?php echo $total_reports; ?></span></a></li>
            <li><a onclick="switchTab('settings', this)"><i class="fa-solid fa-gear"></i> Account Settings</a></li>
            
            <li style="margin-top: 30px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
                <a href="../auth/logout.php" style="color: #EF4444;"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
            </li>
        </ul>
    </aside>

    <!-- Main Admin Content Area -->
    <main class="admin-content">
        
        <!-- OVERVIEW SECTION -->
        <div id="sec-overview" class="admin-section active">
            <div class="section-header">
                <h3><i class="fa-solid fa-chart-pie" style="color: var(--uiu-orange);"></i> System Overview</h3>
            </div>
            <div style="padding: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; background: #fff;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #E0F2FE; color: #0284C7;"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($total_users); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #D1FAE5; color: #059669;"><i class="fa-solid fa-droplet"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($active_blood_reqs); ?></h3>
                        <p>Active Blood Reqs</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #F3E8FF; color: #7C3AED;"><i class="fa-solid fa-briefcase"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($pending_jobs); ?></h3>
                        <p>Pending Jobs</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FEF3C7; color: #D97706;"><i class="fa-solid fa-store"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($pending_items); ?></h3>
                        <p>Pending Items</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- COURSE ASSIGNMENT SECTION (NEW) -->
        <div id="sec-courses" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-book" style="color: #8B5CF6;"></i> Assign Courses to Faculty</h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Course Code & Name</th>
                            <th>Section / Room</th>
                            <th>Schedule</th>
                            <th>Assigned Faculty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get all faculties to populate the dropdown
                        $faculties = [];
                        $fac_res = $conn->query("SELECT id, full_name FROM users WHERE role = 'Faculty' ORDER BY full_name ASC");
                        while($f = $fac_res->fetch_assoc()) { $faculties[] = $f; }

                        $courses_res = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
                        while($c = $courses_res->fetch_assoc()):
                        ?>
                        <tr>
                            <td>
                                <strong style="display:block; font-size: 15px; color: var(--text-main);"><?php echo htmlspecialchars($c['course_code']); ?></strong>
                                <span style="font-size: 13px; color: var(--text-muted);"><?php echo htmlspecialchars($c['course_name']); ?></span>
                            </td>
                            <td>
                                <span class="badge" style="background: #E2E8F0; color: #475569;">Sec: <?php echo htmlspecialchars($c['section'] ?? 'N/A'); ?></span><br>
                                <small style="color: var(--text-muted); margin-top:5px; display:inline-block;">Room: <?php echo htmlspecialchars($c['room_no'] ?? 'N/A'); ?></small>
                            </td>
                            <td><span style="font-size:13px; color:var(--text-main); font-weight:600;"><?php echo htmlspecialchars($c['day_of_week']); ?></span><br><small style="color:var(--text-muted);"><?php echo $c['start_time'] ? date('h:i A', strtotime($c['start_time'])) : 'N/A'; ?></small></td>
                            <td>
                                <select onchange="assignFaculty(<?php echo $c['id']; ?>, this.value)" style="padding: 8px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1); font-size: 13px; font-weight: 600; outline:none; background: #fff; width: 100%; max-width: 200px;">
                                    <option value="0">-- Not Assigned --</option>
                                    <?php foreach($faculties as $fac): ?>
                                        <option value="<?php echo $fac['id']; ?>" <?php echo ($c['faculty_id'] == $fac['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($fac['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SETTINGS SECTION -->
        <div id="sec-settings" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-gear" style="color: #64748b;"></i> Admin Account Settings</h3>
            </div>
            <div style="padding: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; background: #fff;">
                
                <div class="settings-card">
                    <h4><i class="fa-solid fa-id-badge" style="color: #3B82F6;"></i> Update Profile Details</h4>
                    <label>Full Name</label>
                    <input type="text" id="adminUpdateName" class="input-field" value="<?php echo htmlspecialchars($admin_name); ?>">
                    
                    <label>Email Address</label>
                    <input type="email" id="adminUpdateEmail" class="input-field" value="<?php echo htmlspecialchars($admin_email); ?>">
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button class="btn-action btn-view" style="padding: 12px 24px;" onclick="updateAdminProfile()">Save Profile</button>
                    </div>
                </div>

                <div class="settings-card">
                    <h4><i class="fa-solid fa-lock" style="color: #F59E0B;"></i> Change Password</h4>
                    <label>Current Password</label>
                    <input type="password" id="adminOldPass" class="input-field" placeholder="Enter current password">
                    
                    <label>New Password (Min. 6 chars)</label>
                    <input type="password" id="adminNewPass" class="input-field" placeholder="Enter new password">
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button class="btn-action btn-reject" style="padding: 12px 24px;" onclick="updateAdminPassword()">Update Password</button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ANNOUNCEMENTS SECTION -->
        <div id="sec-announcements" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-bullhorn" style="color: #0284C7;"></i> Broadcast Campus Announcement</h3>
            </div>
            <div style="padding: 24px; border-bottom: 1px solid rgba(0,0,0,0.05); background: #f8fafc;">
                <label style="font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; display: block;">Announcement Details</label>
                <textarea id="announceContent" class="input-field" rows="4" placeholder="Write the announcement... It will be pinned to the dashboard and notify all students."></textarea>
                <div style="text-align: right; margin-top: 15px;">
                    <button class="btn-action btn-view" style="padding: 12px 24px; font-size: 14px;" onclick="broadcastAnnouncement()"><i class="fa-solid fa-paper-plane"></i> Broadcast Now</button>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Announcement Content</th>
                            <th>Date Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ann_res = $conn->query("SELECT * FROM posts WHERE post_type = 'Announcement' ORDER BY created_at DESC LIMIT 20");
                        if($ann_res->num_rows > 0):
                            while($a = $ann_res->fetch_assoc()):
                        ?>
                        <tr id="ann-row-<?php echo $a['id']; ?>">
                            <td style="max-width: 500px; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($a['content'])); ?></td>
                            <td><span class="badge" style="background:#E2E8F0; color:#475569;"><?php echo date('M d, Y', strtotime($a['created_at'])); ?></span></td>
                            <td>
                                <button class="btn-action btn-reject" onclick="deleteAnnouncement(<?php echo $a['id']; ?>)"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" style="text-align:center; padding:40px; color:var(--text-muted);">No announcements broadcasted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BLOOD BANK MODERATION SECTION -->
        <div id="sec-bloodbank" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-droplet" style="color: #EF4444;"></i> Blood Bank Moderation</h3>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Patient Info</th>
                            <th>Blood Group & Urgency</th>
                            <th>Hospital & Date</th>
                            <th>Requester</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $blood_res = $conn->query("SELECT b.*, u.full_name FROM blood_requests b JOIN users u ON b.requester_id = u.id WHERE b.status = 'active' ORDER BY b.created_at DESC");
                        if($blood_res->num_rows > 0):
                            while($b = $blood_res->fetch_assoc()):
                                $urg_color = ($b['urgency_level'] == 'Critical') ? 'background:#FEE2E2; color:#DC2626;' : (($b['urgency_level'] == 'Urgent') ? 'background:#FEF3C7; color:#D97706;' : 'background:#E0F2FE; color:#0284C7;');
                        ?>
                        <tr id="blood-row-<?php echo $b['id']; ?>">
                            <td><strong style="display:block; font-size: 15px;"><?php echo htmlspecialchars($b['patient_name']); ?></strong><small style="color:var(--text-muted);"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($b['contact_number']); ?></small></td>
                            <td>
                                <span class="badge" style="background:#EF4444; color:white; font-size: 13px; padding: 4px 12px; margin-right: 8px;"><?php echo htmlspecialchars($b['blood_group']); ?></span>
                                <span class="badge" style="<?php echo $urg_color; ?>"><?php echo htmlspecialchars($b['urgency_level']); ?></span>
                            </td>
                            <td><span style="display:block; font-weight: 500; color:var(--text-main);"><i class="fa-regular fa-hospital"></i> <?php echo htmlspecialchars($b['hospital_name']); ?></span><small style="color:var(--text-muted);">Needed: <?php echo date('M d, Y', strtotime($b['needed_date'])); ?></small></td>
                            <td><a href="../profile.php?id=<?php echo $b['requester_id']; ?>" target="_blank" style="color:var(--text-main); text-decoration:none; font-weight:600;"><?php echo htmlspecialchars($b['full_name']); ?></a></td>
                            <td>
                                <button class="btn-action btn-reject" onclick="deleteBloodReq(<?php echo $b['id']; ?>)" title="Delete as Spam"><i class="fa-solid fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-shield-heart" style="font-size: 32px; color: #10B981; margin-bottom: 10px; display:block;"></i>No active blood requests currently.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- USERS SECTION -->
        <div id="sec-users" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-users" style="color: #3B82F6;"></i> Manage Platform Users</h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>User Profile</th>
                            <th>Email Address</th>
                            <th>Role / Permission</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users_res = $conn->query("SELECT id, full_name, email, role, department FROM users ORDER BY created_at DESC LIMIT 50");
                        while($u = $users_res->fetch_assoc()):
                        ?>
                        <tr id="user-row-<?php echo $u['id']; ?>">
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:36px; height:36px; background:#f1f5f9; border:1px solid rgba(0,0,0,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:800; color:var(--text-main);"><?php echo strtoupper($u['full_name'][0]); ?></div>
                                    <a href="../profile.php?id=<?php echo $u['id']; ?>" target="_blank" style="color:var(--text-main); text-decoration:none; font-weight: 700;"><?php echo htmlspecialchars($u['full_name']); ?></a>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <select onchange="changeRole(<?php echo $u['id']; ?>, this.value)" style="padding: 8px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1); font-size: 13px; font-weight: 600; outline:none; background: #fff;" <?php echo ($u['id'] == $user_id) ? 'disabled' : ''; ?>>
                                    <option value="Student" <?php echo $u['role'] == 'Student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="Faculty" <?php echo $u['role'] == 'Faculty' ? 'selected' : ''; ?>>Faculty</option>
                                    <option value="Admin" <?php echo $u['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </td>
                            <td><span class="badge" style="background:#E2E8F0; color:#475569;"><?php echo htmlspecialchars($u['department'] ?? 'N/A'); ?></span></td>
                            <td>
                                <?php if($u['id'] != $user_id): ?>
                                <button class="btn-action btn-reject" onclick="deleteUser(<?php echo $u['id']; ?>)"><i class="fa-solid fa-trash"></i> Delete User</button>
                                <?php else: ?>
                                <span class="badge" style="background:#D1FAE5; color:#059669;">YOU (ADMIN)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- JOB APPROVALS SECTION -->
        <div id="sec-jobs" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-briefcase" style="color: #7C3AED;"></i> Job & Internship Approvals</h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title & Company</th>
                            <th>Type & Location</th>
                            <th>Posted By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $jobs_res = $conn->query("SELECT j.*, u.full_name FROM jobs j JOIN users u ON j.posted_by = u.id WHERE j.admin_approval_status = 'pending' ORDER BY j.created_at ASC");
                        if($jobs_res->num_rows > 0):
                            while($j = $jobs_res->fetch_assoc()):
                        ?>
                        <tr id="job-row-<?php echo $j['id']; ?>">
                            <td>
                                <strong style="display:block; font-size: 15px; color: var(--text-main);"><?php echo htmlspecialchars($j['title']); ?></strong>
                                <span style="font-size: 13px; color: var(--text-muted);"><i class="fa-regular fa-building"></i> <?php echo htmlspecialchars($j['company']); ?></span>
                            </td>
                            <td>
                                <span class="badge" style="background: #F3E8FF; color: #7C3AED;"><?php echo htmlspecialchars($j['job_type']); ?></span><br>
                                <small style="color: var(--text-muted); display:inline-block; margin-top:5px;"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($j['location']); ?></small>
                            </td>
                            <td><a href="../profile.php?id=<?php echo $j['posted_by']; ?>" target="_blank" style="color:var(--text-main); font-weight: 600; text-decoration:none;"><?php echo htmlspecialchars($j['full_name']); ?></a></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-action btn-accept" onclick="handleJob(<?php echo $j['id']; ?>, 'approve_job')"><i class="fa-solid fa-check"></i> Approve</button>
                                    <button class="btn-action btn-reject" onclick="handleJob(<?php echo $j['id']; ?>, 'reject_job')"><i class="fa-solid fa-xmark"></i> Reject</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-briefcase" style="font-size: 32px; color: #7C3AED; margin-bottom: 10px; opacity:0.5; display:block;"></i>No pending job postings.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MARKETPLACE SECTION -->
        <div id="sec-marketplace" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-store" style="color: #D97706;"></i> Marketplace Approvals</h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Item Details</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pending_res = $conn->query("SELECT m.*, u.full_name FROM marketplace m JOIN users u ON m.seller_id = u.id WHERE m.admin_approval_status = 'pending' ORDER BY m.created_at ASC");
                        if($pending_res->num_rows > 0):
                            while($m = $pending_res->fetch_assoc()):
                        ?>
                        <tr id="market-row-<?php echo $m['id']; ?>">
                            <td>
                                <div style="display:flex; align-items:center; gap:16px;">
                                    <?php if($m['image_path']): ?>
                                        <img src="../<?php echo $m['image_path']; ?>" style="width:56px; height:56px; border-radius:10px; object-fit:cover; border: 1px solid rgba(0,0,0,0.1); box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <?php else: ?>
                                        <div style="width:56px; height:56px; background:#f1f5f9; border-radius:10px; border: 1px solid rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-box" style="color:#94a3b8; font-size: 24px;"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong style="display:block; font-size: 15px; color:var(--text-main); margin-bottom:4px;"><?php echo htmlspecialchars($m['item_title']); ?></strong>
                                        <span class="badge" style="background:#E2E8F0; color:#475569;"><?php echo htmlspecialchars($m['item_condition']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><a href="../profile.php?id=<?php echo $m['seller_id']; ?>" style="color:var(--text-main); font-weight: 600; text-decoration:none;" target="_blank"><?php echo htmlspecialchars($m['full_name']); ?></a></td>
                            <td><strong style="color:var(--uiu-orange); font-size: 16px;">৳<?php echo number_format($m['price']); ?></strong></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-action btn-accept" onclick="handleMarketplace(<?php echo $m['id']; ?>, 'approve_item')"><i class="fa-solid fa-check"></i> Approve</button>
                                    <button class="btn-action btn-reject" onclick="handleMarketplace(<?php echo $m['id']; ?>, 'reject_item')"><i class="fa-solid fa-xmark"></i> Reject</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-store" style="font-size: 32px; color: #F59E0B; margin-bottom: 10px; opacity:0.5; display:block;"></i>All clear! No pending items.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CLUBS SECTION -->
        <div id="sec-clubs" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-people-group" style="color: #14B8A6;"></i> Manage Campus Clubs</h3>
            </div>
            
            <div style="padding: 24px; border-bottom: 1px solid rgba(0,0,0,0.05); background: #f8fafc;">
                <label style="font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 15px; display: block;">Create New Club</label>
                <div style="display: flex; gap: 20px; align-items: flex-end;">
                    <div style="flex: 2;">
                        <label style="font-size: 11px; font-weight: 700; text-transform:uppercase; color: var(--text-muted);">Club Name</label>
                        <input type="text" id="clubName" class="input-field" placeholder="e.g. UIU Computer Club">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 11px; font-weight: 700; text-transform:uppercase; color: var(--text-muted);">Category</label>
                        <select id="clubCat" class="input-field" style="cursor: pointer;">
                            <option value="Technology">Technology</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Business">Business</option>
                            <option value="Sports">Sports</option>
                            <option value="Social">Social</option>
                        </select>
                    </div>
                    <div style="flex: 3;">
                        <label style="font-size: 11px; font-weight: 700; text-transform:uppercase; color: var(--text-muted);">Short Description</label>
                        <input type="text" id="clubDesc" class="input-field" placeholder="Brief info about the club...">
                    </div>
                    <div>
                        <button class="btn-action btn-view" style="padding: 12px 24px;" onclick="createClub()"><i class="fa-solid fa-plus"></i> Create Club</button>
                    </div>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Club Name</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="clubTableBody">
                        <?php
                        $clubs_res = $conn->query("SELECT * FROM clubs ORDER BY created_at DESC");
                        if($clubs_res->num_rows > 0):
                            while($c = $clubs_res->fetch_assoc()):
                        ?>
                        <tr id="club-row-<?php echo $c['id']; ?>">
                            <td><strong style="font-size: 15px; color: var(--text-main);"><?php echo htmlspecialchars($c['name']); ?></strong></td>
                            <td><span class="badge" style="background: #E2E8F0; color: #475569;"><?php echo htmlspecialchars($c['category']); ?></span></td>
                            <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($c['description']); ?></td>
                            <td>
                                <button class="btn-action btn-reject" onclick="deleteClub(<?php echo $c['id']; ?>)"><i class="fa-solid fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr id="no-club-row"><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-people-group" style="font-size: 32px; opacity:0.5; margin-bottom:10px; display:block;"></i>No clubs created yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- REPORTS SECTION -->
        <div id="sec-reports" class="admin-section">
            <div class="section-header">
                <h3><i class="fa-solid fa-flag" style="color: #DC2626;"></i> Reported Posts Management</h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Report Count</th>
                            <th>Post Content Snippet</th>
                            <th>Author</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $reports_res = $conn->query("SELECT p.id as post_id, p.content, u.full_name, u.id as author_id, COUNT(r.id) as report_count FROM posts p JOIN post_reports r ON p.id = r.post_id JOIN users u ON p.user_id = u.id GROUP BY p.id ORDER BY report_count DESC");
                        if($reports_res->num_rows > 0):
                            while($r = $reports_res->fetch_assoc()):
                        ?>
                        <tr id="report-row-<?php echo $r['post_id']; ?>">
                            <td>
                                <div style="display:inline-flex; align-items:center; background: #FEE2E2; color: #DC2626; padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight:800; border: 1px solid rgba(220,38,38,0.2);">
                                    <i class="fa-solid fa-flag" style="margin-right: 6px;"></i> <?php echo $r['report_count']; ?>
                                </div>
                            </td>
                            <td style="max-width:350px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-style: italic; color: #475569;">"<?php echo htmlspecialchars($r['content']); ?>"</td>
                            <td><a href="../profile.php?id=<?php echo $r['author_id']; ?>" style="color:var(--text-main); font-weight: 600; text-decoration:none;" target="_blank"><?php echo htmlspecialchars($r['full_name']); ?></a></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-action btn-accept" onclick="handleReport(<?php echo $r['post_id']; ?>, 'dismiss_report')" title="Dismiss Reports"><i class="fa-solid fa-check-double"></i></button>
                                    <button class="btn-action btn-reject" onclick="handleReport(<?php echo $r['post_id']; ?>, 'delete_reported_post')" title="Delete Post"><i class="fa-solid fa-trash"></i></button>
                                    <a href="../dashboard.php#post-card-<?php echo $r['post_id']; ?>" target="_blank" class="btn-action btn-view" title="View Full Post"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-shield-heart" style="font-size: 32px; color: #3B82F6; margin-bottom: 10px; opacity:0.5; display:block;"></i>Community is safe! No reported posts currently.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script>
    function showToast(message, isError = false) {
        let toast = document.getElementById("toastMessage");
        let icon = document.getElementById("toastIcon");
        document.getElementById("toastText").innerText = message;
        
        if (isError) {
            icon.className = "fa-solid fa-xmark";
            icon.style.color = "#EF4444";
        } else {
            icon.className = "fa-solid fa-check";
            icon.style.color = "#10B981";
        }

        toast.className = "toast show";
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    }

    function switchTab(tabId, btn) {
        document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
        document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('sec-' + tabId).classList.add('active');
    }

    function updateAdminProfile() {
        let name = document.getElementById('adminUpdateName').value.trim();
        let email = document.getElementById('adminUpdateEmail').value.trim();
        
        let fd = new FormData();
        fd.append('ajax_action', 'update_admin_profile');
        fd.append('name', name);
        fd.append('email', email);
        
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            showToast(data.message, data.status === 'error');
            if(data.status === 'success') setTimeout(() => location.reload(), 1000);
        });
    }

    function updateAdminPassword() {
        let oldPass = document.getElementById('adminOldPass').value;
        let newPass = document.getElementById('adminNewPass').value;
        
        if(oldPass === '' || newPass === '') {
            showToast("Passwords cannot be empty.", true);
            return;
        }

        let fd = new FormData();
        fd.append('ajax_action', 'update_admin_password');
        fd.append('old_pass', oldPass);
        fd.append('new_pass', newPass);
        
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            showToast(data.message, data.status === 'error');
            if(data.status === 'success') {
                document.getElementById('adminOldPass').value = '';
                document.getElementById('adminNewPass').value = '';
            }
        });
    }

    function assignFaculty(courseId, facultyId) {
        let fd = new FormData();
        fd.append('ajax_action', 'assign_faculty');
        fd.append('course_id', courseId);
        fd.append('faculty_id', facultyId);

        fetch('dashboard_2.php', { method: 'POST', body: fd })
        .then(r=>r.json())
        .then(data => {
            if(data.status === 'success') showToast(data.message);
        });
    }

    function handleMarketplace(itemId, action) {
        if(!confirm("Are you sure?")) return;
        let fd = new FormData(); fd.append('ajax_action', action); fd.append('item_id', itemId);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') { document.getElementById('market-row-' + itemId).remove(); showToast(data.message); }
        });
    }

    function handleJob(jobId, action) {
        if(!confirm("Are you sure?")) return;
        let fd = new FormData(); fd.append('ajax_action', action); fd.append('job_id', jobId);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') { document.getElementById('job-row-' + jobId).remove(); showToast(data.message); }
        });
    }

    function handleReport(postId, action) {
        if(!confirm("Are you sure?")) return;
        let fd = new FormData(); fd.append('ajax_action', action); fd.append('post_id', postId);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') { document.getElementById('report-row-' + postId).remove(); showToast(data.message); }
        });
    }

    function deleteUser(userId) {
        if(!confirm("WARNING: This will permanently delete the user and all their data! Proceed?")) return;
        let fd = new FormData(); fd.append('ajax_action', 'delete_user'); fd.append('target_user', userId);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') { document.getElementById('user-row-' + userId).remove(); showToast("User deleted completely."); }
        });
    }

    function changeRole(userId, newRole) {
        if(!confirm("Change role to " + newRole + "?")) return;
        let fd = new FormData(); fd.append('ajax_action', 'change_role'); fd.append('target_user', userId); fd.append('new_role', newRole);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') { showToast("User role updated to " + newRole); }
        });
    }

    function createClub() {
        let name = document.getElementById('clubName').value.trim();
        let cat = document.getElementById('clubCat').value;
        let desc = document.getElementById('clubDesc').value.trim();
        if(name === '' || desc === '') { alert("Please fill all fields"); return; }

        let fd = new FormData(); fd.append('ajax_action', 'create_club'); fd.append('club_name', name); fd.append('club_category', cat); fd.append('club_desc', desc);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') {
                showToast(data.message);
                setTimeout(() => location.reload(), 1000);
            }
        });
    }

    function deleteClub(clubId) {
        if(!confirm("Delete this club?")) return;
        let fd = new FormData(); fd.append('ajax_action', 'delete_club'); fd.append('club_id', clubId);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') { document.getElementById('club-row-' + clubId).remove(); showToast(data.message); }
        });
    }

    function broadcastAnnouncement() {
        let content = document.getElementById('announceContent').value.trim();
        if(content === '') { alert("Announcement cannot be empty!"); return; }

        let fd = new FormData(); fd.append('ajax_action', 'broadcast_announcement'); fd.append('content', content);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') {
                showToast(data.message);
                document.getElementById('announceContent').value = '';
                setTimeout(() => location.reload(), 1000);
            }
        });
    }

    function deleteAnnouncement(postId) {
        if(!confirm("Delete this announcement?")) return;
        let fd = new FormData(); fd.append('ajax_action', 'delete_announcement'); fd.append('post_id', postId);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') { document.getElementById('ann-row-' + postId).remove(); showToast(data.message); }
        });
    }

    function deleteBloodReq(reqId) {
        if(!confirm("Delete this blood request? Use this only for spam/fake requests.")) return;
        let fd = new FormData(); fd.append('ajax_action', 'delete_blood_request'); fd.append('req_id', reqId);
        fetch('dashboard_2.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data=>{
            if(data.status === 'success') { document.getElementById('blood-row-' + reqId).remove(); showToast(data.message); }
        });
    }
</script>
</body>
</html>