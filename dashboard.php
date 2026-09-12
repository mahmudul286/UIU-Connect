<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$current_user_name = $_SESSION['full_name'];
$words = explode(" ", $current_user_name);
$current_initials = strtoupper($words[0][0] . (isset($words[1]) ? $words[1][0] : ''));
$today_day = date('l'); 
$tomorrow_day = date('l', strtotime('+1 day'));

$curr_user_res = $conn->query("SELECT profile_pic FROM users WHERE id = $user_id")->fetch_assoc();
$current_profile_pic = $curr_user_res['profile_pic'] ?? null;


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    // 1. LIVE SEARCH
    if ($action == 'live_search') {
        $query = $conn->real_escape_string($_POST['query']);
        $html = '';

        // Search Users
        $sql_users = "SELECT id, full_name, role, department, profile_pic FROM users WHERE full_name LIKE '%$query%' LIMIT 3";
        $res_users = $conn->query($sql_users);
        if($res_users->num_rows > 0) {
            $html .= '<div style="padding: 10px 15px; font-size: 11px; font-weight: 700; color: var(--text-muted); background: var(--bg-light); border-bottom: 1px solid var(--border-light); text-transform: uppercase;">People</div>';
            while($u = $res_users->fetch_assoc()) {
                $u_initial = strtoupper($u['full_name'][0]);
                $avatar = $u['profile_pic'] ? '<img src="'.htmlspecialchars($u['profile_pic']).'" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">' : $u_initial;
                
                $html .= '<a href="profile.php?id='.$u['id'].'" style="display:flex; align-items:center; gap:10px; padding: 12px 15px; border-bottom:1px solid var(--border-light); text-decoration:none; color:var(--text-main); transition:0.2s;">
                            <div style="width:32px; height:32px; background:var(--uiu-orange-light); color:var(--uiu-orange); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold;">'.$avatar.'</div>
                            <div>
                                <strong style="font-size:14px; display:block;">'.htmlspecialchars($u['full_name']).'</strong>
                                <small style="color:var(--text-muted);">'.htmlspecialchars($u['role']).' • '.htmlspecialchars($u['department']).'</small>
                            </div>
                          </a>';
            }
        }

        // Search Posts
        $sql_posts = "SELECT p.id, p.content, u.full_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.content LIKE '%$query%' ORDER BY p.created_at DESC LIMIT 4";
        $res_posts = $conn->query($sql_posts);
        if($res_posts->num_rows > 0) {
            $html .= '<div style="padding: 10px 15px; font-size: 11px; font-weight: 700; color: var(--text-muted); background: var(--bg-light); border-bottom: 1px solid var(--border-light); border-top: 1px solid var(--border-light); text-transform: uppercase;">Posts</div>';
            while($p = $res_posts->fetch_assoc()) {
                $snippet = htmlspecialchars(mb_strimwidth($p['content'], 0, 60, "..."));
                $html .= '<a href="dashboard.php#post-card-'.$p['id'].'" style="display:flex; align-items:center; gap:10px; padding: 12px 15px; border-bottom:1px solid var(--border-light); text-decoration:none; color:var(--text-main); transition:0.2s;">
                            <div style="width:32px; height:32px; background:#f1f5f9; color:#64748b; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H14"></path></svg>
                            </div>
                            <div style="overflow: hidden;">
                                <strong style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:2px;">'.$p['full_name'].'</strong>
                                <span style="font-size:14px; font-weight:500; display:block; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">"'.$snippet.'"</span>
                            </div>
                          </a>';
            }
        }

        //  Marketplace
        $sql_mp = "SELECT id, item_title, price FROM marketplace WHERE item_title LIKE '%$query%' AND admin_approval_status = 'approved' LIMIT 3";
        $res_mp = $conn->query($sql_mp);
        if($res_mp->num_rows > 0) {
            $html .= '<div style="padding: 10px 15px; font-size: 11px; font-weight: 700; color: var(--text-muted); background: var(--bg-light); border-bottom: 1px solid var(--border-light); border-top: 1px solid var(--border-light); text-transform: uppercase;">Marketplace</div>';
            while($m = $res_mp->fetch_assoc()) {
                $html .= '<a href="marketplace.php" style="display:flex; align-items:center; justify-content: space-between; padding: 12px 15px; border-bottom:1px solid var(--border-light); text-decoration:none; color:var(--text-main); transition:0.2s;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <span style="font-size:14px; font-weight:500;">'.htmlspecialchars($m['item_title']).'</span>
                            </div>
                            <span style="font-size:13px; color:var(--uiu-orange); font-weight:600;">৳'.number_format($m['price']).'</span>
                          </a>';
            }
        }

        if($html === '') {
            $html = '<div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 14px;">No results found</div>';
        }
        
        echo json_encode(['html' => $html]); exit();
    }
    
    // 2. POST LIKE
    if ($action == 'toggle_like') {
        $post_id = intval($_POST['post_id']);
        $check = $conn->query("SELECT id FROM likes WHERE user_id = $user_id AND post_id = $post_id");
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id");
            $is_liked = false;
        } else {
            $conn->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
            $is_liked = true;
        }
        $count = $conn->query("SELECT COUNT(id) as total FROM likes WHERE post_id = $post_id")->fetch_assoc()['total'];
        echo json_encode(['status' => 'success', 'is_liked' => $is_liked, 'count' => $count]); exit();
    }

    // 3. COMMENT LIKE
    if ($action == 'toggle_comment_like') {
        $comment_id = intval($_POST['comment_id']);
        $check = $conn->query("SELECT id FROM comment_likes WHERE user_id = $user_id AND comment_id = $comment_id");
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM comment_likes WHERE user_id = $user_id AND comment_id = $comment_id");
            $is_liked = false;
        } else {
            $conn->query("INSERT INTO comment_likes (user_id, comment_id) VALUES ($user_id, $comment_id)");
            $is_liked = true;
        }
        $count = $conn->query("SELECT COUNT(id) as c FROM comment_likes WHERE comment_id = $comment_id")->fetch_assoc()['c'];
        echo json_encode(['status' => 'success', 'is_liked' => $is_liked, 'count' => $count]); exit();
    }
    
    // 4. COMMENT / REPLY
    if ($action == 'add_comment') {
        $post_id = intval($_POST['post_id']);
        $text = $conn->real_escape_string($_POST['comment_text']);
        $parent_id = (isset($_POST['parent_id']) && $_POST['parent_id'] !== '') ? intval($_POST['parent_id']) : 'NULL';
        $resolved_parent_id = $parent_id;
        
        if ($parent_id !== 'NULL') {
            $check_parent = $conn->query("SELECT parent_id FROM comments WHERE id = $parent_id")->fetch_assoc();
            if ($check_parent && $check_parent['parent_id'] !== NULL) { $resolved_parent_id = $check_parent['parent_id']; }
        }
        
        $conn->query("INSERT INTO comments (post_id, user_id, comment_text, parent_id) VALUES ($post_id, $user_id, '$text', $resolved_parent_id)");
        $new_comment_id = $conn->insert_id;
        $total_comments = $conn->query("SELECT COUNT(id) as c FROM comments WHERE post_id = $post_id")->fetch_assoc()['c'];
        
        $margin_style = ($resolved_parent_id !== 'NULL') ? 'margin-left: 48px; margin-top: 8px;' : 'margin-bottom: 12px;';
        $avatar_size = ($resolved_parent_id !== 'NULL') ? '24px' : '32px';
        $font_size = ($resolved_parent_id !== 'NULL') ? '10px' : '12px';
        
        $avatar_html = $current_profile_pic ? '<img src="'.htmlspecialchars($current_profile_pic).'" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">' : $current_initials;

        $html = '
        <div style="display: flex; gap: 10px; '.$margin_style.'">
            <a href="profile.php?id='.$user_id.'" style="width: '.$avatar_size.'; height: '.$avatar_size.'; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: '.$font_size.'; background: var(--bg-light); border: 1px solid var(--border-light); color: var(--text-main); flex-shrink: 0; text-decoration: none; overflow:hidden;">'.$avatar_html.'</a>
            <div style="flex: 1;">
                <div style="background: var(--bg-light); padding: 8px 12px; border-radius: 16px; display: inline-block;">
                    <a href="profile.php?id='.$user_id.'" style="font-size: 13px; color: var(--text-main); font-weight: 600; text-decoration: none;">'.$current_user_name.'</a>
                    <p style="font-size: 14px; color: var(--text-main); margin-top: 2px;">'.htmlspecialchars($text).'</p>
                </div>
                <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-left: 12px; margin-top: 4px; display:flex; gap:15px; align-items:center;">
                    <span style="cursor:pointer;" onclick="toggleCommentLike('.$new_comment_id.', this)">Like <span id="clike-count-'.$new_comment_id.'"></span></span>
                    <span style="cursor:pointer;" onclick="focusReply('.$post_id.', '.($resolved_parent_id !== 'NULL' ? $resolved_parent_id : $new_comment_id).', \''.addslashes($current_user_name).'\')">Reply</span>
                    <span style="font-weight:400;">Just now</span>
                </div>
            </div>
        </div>';
        
        echo json_encode(['status' => 'success', 'html' => $html, 'total_comments' => $total_comments, 'resolved_parent_id' => $resolved_parent_id]); exit();
    }

    // 5. POLL VOTE
    if ($action == 'vote_poll') {
        $post_id = intval($_POST['post_id']);
        $option_index = intval($_POST['option_index']);
        $check = $conn->query("SELECT id FROM poll_votes WHERE post_id = $post_id AND user_id = $user_id");
        if($check->num_rows == 0) { $conn->query("INSERT INTO poll_votes (post_id, option_index, user_id) VALUES ($post_id, $option_index, $user_id)"); }
        
        $total = $conn->query("SELECT COUNT(id) as c FROM poll_votes WHERE post_id = $post_id")->fetch_assoc()['c'];
        $results = [];
        for($i=0; $i<10; $i++) {
            $opt_count = $conn->query("SELECT COUNT(id) as c FROM poll_votes WHERE post_id = $post_id AND option_index = $i")->fetch_assoc()['c'];
            if($total > 0) $results[$i] = round(($opt_count / $total) * 100);
        }
        echo json_encode(['status' => 'success', 'results' => $results, 'total' => $total]); exit();
    }

    // 6. SAVE
    if ($action == 'toggle_save') {
        $post_id = intval($_POST['post_id']);
        $check = $conn->query("SELECT id FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id");
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id");
            echo json_encode(['status' => 'success', 'saved' => false, 'message' => 'Post removed from saved collection.']);
        } else {
            $conn->query("INSERT INTO saved_posts (user_id, post_id) VALUES ($user_id, $post_id)");
            echo json_encode(['status' => 'success', 'saved' => true, 'message' => 'Post saved successfully!']);
        } exit();
    }

    // 7. DELETE POST
    if ($action == 'delete_post') {
        $post_id = intval($_POST['post_id']);
        $check = $conn->query("SELECT id FROM posts WHERE id = $post_id AND user_id = $user_id");
        if($check->num_rows > 0){
            $conn->query("DELETE FROM posts WHERE id = $post_id");
            echo json_encode(['status' => 'success']);
        } else { echo json_encode(['status' => 'error']); }
        exit();
    }

    // 8. EDIT POST
    if ($action == 'edit_post') {
        $post_id = intval($_POST['post_id']);
        $new_content = $conn->real_escape_string($_POST['content']);
        $old = $conn->query("SELECT content FROM posts WHERE id = $post_id AND user_id = $user_id")->fetch_assoc();
        if($old){
            $old_content = $conn->real_escape_string($old['content']);
            $conn->query("INSERT INTO post_edit_history (post_id, old_content) VALUES ($post_id, '$old_content')");
            $conn->query("UPDATE posts SET content = '$new_content', is_edited = 1 WHERE id = $post_id");
            echo json_encode(['status' => 'success', 'new_content' => htmlspecialchars($_POST['content'])]);
        } exit();
    }

    // 9.EDIT HISTORY
    if ($action == 'get_edit_history') {
        $post_id = intval($_POST['post_id']);
        $history = $conn->query("SELECT old_content, edited_at FROM post_edit_history WHERE post_id = $post_id ORDER BY edited_at DESC");
        $html = '';
        if($history->num_rows > 0){
            while($h = $history->fetch_assoc()){
                $html .= '<div style="margin-bottom:15px; padding-bottom:15px; border-bottom:1px solid var(--border-light); text-align:left;">';
                $html .= '<small style="color:var(--text-muted); display:block; margin-bottom:5px;">'.date('M d, Y h:i A', strtotime($h['edited_at'])).'</small>';
                $html .= '<p style="font-size:14px; color:var(--text-main); white-space:pre-wrap;">'.htmlspecialchars($h['old_content']).'</p></div>';
            }
        } else { $html = '<p style="color:var(--text-muted); text-align:center;">No edit history found.</p>'; }
        echo json_encode(['status' => 'success', 'html' => $html]); exit();
    }

    // 10. SHARE POST
    if ($action == 'share_post') {
        $post_id = intval($_POST['post_id']);
        $share_text = $conn->real_escape_string($_POST['share_text']);
        $conn->query("INSERT INTO posts (user_id, post_type, content, shared_from_id) VALUES ($user_id, 'General', '$share_text', $post_id)");
        echo json_encode(['status' => 'success']); exit();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_content']) && !isset($_POST['ajax_action'])) {
    $content = $conn->real_escape_string($_POST['post_content']);
    $post_type = (isset($_POST['is_announcement']) && $user_role === 'Admin') ? 'Announcement' : 'General';
    $media_path = NULL; $media_type = NULL; $poll_data = NULL;

    if (isset($_FILES['media_upload']) && $_FILES['media_upload']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . '_' . basename($_FILES["media_upload"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (move_uploaded_file($_FILES["media_upload"]["tmp_name"], $target_file)) {
            $media_path = $target_file;
            if(in_array($file_ext, ['jpg','jpeg','png','gif'])) $media_type = 'image';
            elseif(in_array($file_ext, ['mp4','webm'])) $media_type = 'video';
            else $media_type = 'document';
        }
    }

    if (isset($_POST['poll_options']) && !empty($_POST['poll_options'][0])) {
        $options = array_filter($_POST['poll_options']);
        $poll_array = [];
        foreach($options as $opt) { $poll_array[] = ['text' => $conn->real_escape_string($opt)]; }
        if(count($poll_array) > 0) $poll_data = json_encode($poll_array);
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, post_type, content, media_path, media_type, poll_data) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $post_type, $content, $media_path, $media_type, $poll_data);
    $stmt->execute();
    header("Location: dashboard.php"); exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ocr_sync_complete'])) {
    $extracted_text = $_POST['extracted_courses']; 
    if (!empty($extracted_text)) {
        $course_codes = explode(',', $extracted_text);
        foreach($course_codes as $code) {
            $clean_code = preg_replace('/\s+/', '', strtoupper(trim($code))); 
            $stmt = $conn->prepare("SELECT id FROM courses WHERE REPLACE(course_code, ' ', '') = ?");
            $stmt->bind_param("s", $clean_code);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $course_id = $row['id'];
                $check = $conn->query("SELECT id FROM enrollments WHERE student_id = $user_id AND course_id = $course_id");
                if($check->num_rows == 0) $conn->query("INSERT INTO enrollments (student_id, course_id) VALUES ($user_id, $course_id)");
            }
        }
    }
    $_SESSION['setup_bypassed'] = true; 
    header("Location: dashboard.php"); exit();
}

$is_setup_complete = true;
if ($user_role === 'Student' && !isset($_SESSION['setup_bypassed'])) {
    $check_setup = $conn->query("SELECT id FROM enrollments WHERE student_id = $user_id LIMIT 1");
    if ($check_setup && $check_setup->num_rows === 0) { $is_setup_complete = false; }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">

<div id="toastMessage" class="toast">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #10B981;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
    <span id="toastText">Action successful</span>
</div>

<?php if (!$is_setup_complete): ?>
<div class="modal-overlay" id="setupModal" style="display: flex;">
    <div class="modal-content" id="modalStep1" style="text-align: center;">
        <h2 style="font-size: 24px; color: var(--text-main); margin-bottom: 10px; display:flex; align-items:center; justify-content: center; gap:8px;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--uiu-orange);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
            Sync Your Trimester
        </h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">Upload your ELMS routine image. Our OCR engine will extract your course codes automatically.</p>
        
        <div style="border: 2px dashed var(--border-light); border-radius: 12px; padding: 40px 20px; background: var(--bg-light); cursor: pointer; margin-bottom: 20px;" onclick="document.getElementById('routineUpload').click()">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: var(--text-muted); margin-bottom: 10px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            <h4 style="font-size: 15px; color: var(--text-main); margin-bottom: 5px;">Click to upload routine</h4>
            <p style="font-size: 13px; color: var(--text-muted);">Supports PNG, JPG</p>
            <input type="file" id="routineUpload" style="display: none;" accept="image/*" onchange="startRealOCR(event)">
        </div>
        <button onclick="skipSetup()" style="background: none; border: none; color: var(--text-muted); font-size: 13px; cursor: pointer; text-decoration: underline;">Skip for now</button>
    </div>

    <div class="modal-content" id="modalStep2" style="display: none; text-align: center;">
        <svg width="50" height="50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: var(--uiu-orange); margin-bottom: 20px; animation: pulse 1.5s infinite;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
        <h2 style="font-size: 18px; color: var(--text-main); margin-bottom: 20px;">AI Scanning in Progress...</h2>
        <div style="width: 100%; height: 6px; background: var(--border-light); border-radius: 10px; overflow: hidden; margin-bottom: 15px;">
            <div id="scanProgress" style="width: 0%; height: 100%; background: var(--uiu-orange); transition: 0.1s;"></div>
        </div>
        <div style="font-size: 13px; color: var(--text-muted); font-family: monospace;" id="scanLog">Loading OCR...</div>
        <form method="POST" id="ocrForm">
            <input type="hidden" name="ocr_sync_complete" value="1">
            <input type="hidden" name="extracted_courses" id="extractedCourses" value="">
        </form>
    </div>
</div>
<?php endif; ?>

<!-- MAIN DASHBOARD CONTENT -->
<main class="dashboard-layout">
    
    <div class="feed-column">
        <div class="card" style="margin-bottom: 24px; padding: 20px; border-radius: 12px;">
            <form method="POST" action="dashboard.php" enctype="multipart/form-data">
                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <a href="profile.php?id=<?php echo $user_id; ?>" class="avatar-sm" style="background: var(--uiu-orange-light); color: var(--uiu-orange); text-decoration: none; overflow: hidden;">
                        <?php if($current_profile_pic): ?>
                            <img src="<?php echo htmlspecialchars($current_profile_pic); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: echo $current_initials; endif; ?>
                    </a>
                    <div style="flex: 1;">
                        <textarea name="post_content" class="composer-input" placeholder="What's happening in UIU, <?php echo $words[0]; ?>?" required rows="2"></textarea>
                        
                        <input type="file" name="media_upload" id="mediaUpload" style="display:none;" onchange="previewMedia(this)">
                        
                        <div id="mediaPreview" class="preview-area">
                            <button type="button" class="close-preview" onclick="clearMedia()"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            <img id="imgPreview" style="max-width:100%; max-height:250px; border-radius:8px; display:none; object-fit: contain;">
                            <p id="docPreview" style="display:none; font-size: 14px; font-weight: 600; color: var(--text-main);">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg> Document Attached
                            </p>
                        </div>

                        <div id="pollCreator" class="preview-area">
                            <button type="button" class="close-preview" onclick="clearPoll()"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; color: var(--text-main); font-weight: 600;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg> Create a Poll
                            </div>
                            <div id="pollOptionsContainer">
                                <input type="text" name="poll_options[]" class="poll-input" placeholder="Option 1">
                                <input type="text" name="poll_options[]" class="poll-input" placeholder="Option 2">
                            </div>
                            <button type="button" onclick="addPollOption()" style="background:none; border:none; color:var(--uiu-orange); font-size:13px; font-weight:600; cursor:pointer; margin-top:5px; display:flex; align-items:center; gap:5px;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg> Add Option
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-light); padding-top: 12px;">
                    <div class="composer-tools">
                        <button type="button" class="tool-btn" style="color: #10B981;" onclick="document.getElementById('mediaUpload').click();">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Photo/Doc
                        </button>
                        <button type="button" class="tool-btn" style="color: #F59E0B;" onclick="document.getElementById('pollCreator').style.display='block';">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg> Poll
                        </button>
                        <?php if($user_role === 'Admin' || $user_role === 'Faculty'): ?>
                        <label style="display:flex; align-items:center; gap:5px; font-size:13px; color:var(--text-muted); cursor:pointer; margin-left:10px;">
                            <input type="checkbox" name="is_announcement" value="1"> Announcement
                        </label>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary" style="padding: 8px 24px;">Post</button>
                </div>
            </form>
        </div>

        <?php
        $sql = "SELECT p.*, u.full_name, u.role, u.department, u.profile_pic FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $post_id = $row['id'];
                $author_words = explode(" ", $row['full_name']);
                $initials = strtoupper($author_words[0][0] . (isset($author_words[1]) ? $author_words[1][0] : ''));
                
                $is_liked = $conn->query("SELECT id FROM likes WHERE post_id = $post_id AND user_id = $user_id")->num_rows > 0;
                $like_count = $conn->query("SELECT COUNT(id) as c FROM likes WHERE post_id = $post_id")->fetch_assoc()['c'];
                $is_saved = $conn->query("SELECT id FROM saved_posts WHERE post_id = $post_id AND user_id = $user_id")->num_rows > 0;
                $comment_count = $conn->query("SELECT COUNT(id) as c FROM comments WHERE post_id = $post_id")->fetch_assoc()['c'];
                ?>
                <div class="post-card" id="post-card-<?php echo $post_id; ?>">
                    <!-- Header -->
                    <div class="post-header">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <a href="profile.php?id=<?php echo $row['user_id']; ?>" class="avatar-sm" style="background: var(--bg-light); border: 1px solid var(--border-light); color: var(--text-main); text-decoration: none; overflow: hidden;">
                                <?php if($row['profile_pic']): ?>
                                    <img src="<?php echo htmlspecialchars($row['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: echo $initials; endif; ?>
                            </a>
                            <div style="line-height: 1.2;">
                                <a href="profile.php?id=<?php echo $row['user_id']; ?>" style="font-size: 15px; color: var(--text-main); font-weight: 600; text-decoration: none;"><?php echo htmlspecialchars($row['full_name']); ?></a><br>
                                <small style="color: var(--text-muted); font-size: 13px;">
                                    <?php echo htmlspecialchars($row['role']) . " • " . htmlspecialchars($row['department']); ?>
                                    <span style="margin: 0 4px;">·</span> <?php echo date('M d, h:i A', strtotime($row['created_at'])); ?>
                                    
                                    <?php if($row['is_edited']): ?>
                                        <span style="margin-left: 5px; cursor: pointer; text-decoration: underline;" onclick="viewEditHistory(<?php echo $post_id; ?>)">(Edited)</span>
                                    <?php endif; ?>

                                    <?php if($row['post_type'] === 'Announcement'): ?>
                                        <span style="margin-left: 5px; color: #EF4444; font-weight: 600;">
                                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20" style="vertical-align: middle;"><path d="M8 16a2 2 0 001.996-1.85L10 14h4c1.103 0 2-.897 2-2V6c0-1.103-.897-2-2-2H6C4.897 4 4 4.897 4 6v6c0 1.103.897 2 2 2h4l-.004.15A2.002 2.002 0 008 16zm-2-2v-6h8v6H6z"></path></svg> Announcement
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        
                        <?php if($row['user_id'] == $user_id): ?>
                        <div style="position: relative;">
                            <button style="background:none; border:none; color:var(--text-muted); cursor:pointer;" onclick="toggleMenu('post-menu-<?php echo $post_id; ?>')">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                            </button>
                            <div id="post-menu-<?php echo $post_id; ?>" class="post-options-menu" onmouseleave="this.style.display='none'">
                                <button onclick="openEditPost(<?php echo $post_id; ?>)">Edit Post</button>
                                <button style="color:#EF4444;" onclick="openDeleteModal(<?php echo $post_id; ?>)">Delete Post</button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <p id="post-content-<?php echo $post_id; ?>" class="post-content-text" data-raw="<?php echo htmlspecialchars($row['content']); ?>"><?php echo htmlspecialchars($row['content']); ?></p>

                    <!-- Media -->
                    <?php if($row['media_path']): ?>
                        <div class="post-media">
                            <?php if($row['media_type'] == 'image'): ?>
                                <img src="<?php echo $row['media_path']; ?>">
                            <?php elseif($row['media_type'] == 'video'): ?>
                                <video controls style="width: 100%; border-radius: 8px;"><source src="<?php echo $row['media_path']; ?>"></video>
                            <?php else: ?>
                                <div style="padding:15px; background:var(--bg-light); border:1px solid var(--border-light); border-radius:8px; display: flex; align-items: center; gap: 8px;">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--uiu-orange);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <a href="<?php echo $row['media_path']; ?>" target="_blank" style="color:var(--text-main); font-weight:600; text-decoration: none;">Download Document</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Poll -->
                    <?php if($row['poll_data']): 
                        $poll_opts = json_decode($row['poll_data'], true); 
                        $has_voted = $conn->query("SELECT id FROM poll_votes WHERE post_id = $post_id AND user_id = $user_id")->num_rows > 0;
                        $total_votes = $conn->query("SELECT COUNT(id) as c FROM poll_votes WHERE post_id = $post_id")->fetch_assoc()['c'];
                    ?>
                        <div class="poll-wrapper" id="poll-wrapper-<?php echo $post_id; ?>">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; font-weight: 600; color: var(--text-main);">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--uiu-orange);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg> Campus Poll
                            </div>
                            <?php foreach($poll_opts as $index => $opt): 
                                $opt_votes = $conn->query("SELECT COUNT(id) as c FROM poll_votes WHERE post_id = $post_id AND option_index = $index")->fetch_assoc()['c'];
                                $pct = ($total_votes > 0) ? round(($opt_votes / $total_votes) * 100) : 0;
                            ?>
                                <div class="poll-option-row" onclick="<?php echo !$has_voted ? "submitVote($post_id, $index)" : ""; ?>">
                                    <div class="poll-progress" id="poll-prog-<?php echo $post_id; ?>-<?php echo $index; ?>" style="width: <?php echo $has_voted ? $pct : 0; ?>%;"></div>
                                    <div class="poll-content">
                                        <span style="font-size:14px; font-weight:500;"><?php echo htmlspecialchars($opt['text']); ?></span>
                                        <?php if($has_voted): ?>
                                            <span style="font-size:13px; font-weight:600; color:var(--text-main);" id="poll-pct-<?php echo $post_id; ?>-<?php echo $index; ?>"><?php echo $pct; ?>%</span>
                                        <?php else: ?>
                                            <div style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--border-light);"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <small style="color: var(--text-muted); font-size: 12px; margin-top: 8px; display: block;" id="poll-total-<?php echo $post_id; ?>"><?php echo $total_votes; ?> votes</small>
                        </div>
                    <?php endif; ?>

                    <?php if($row['shared_from_id']): 
                        $orig = $conn->query("SELECT p.content, u.full_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ".$row['shared_from_id'])->fetch_assoc();
                        if($orig):
                    ?>
                        <div style="margin: 0 20px 16px 20px; padding: 16px; border: 1px solid var(--border-light); border-radius: 12px; background: var(--bg-light);">
                            <strong style="font-size: 13px; color: var(--text-main); display: block; margin-bottom: 8px;">Original post by <?php echo htmlspecialchars($orig['full_name']); ?></strong>
                            <p style="font-size: 14px; color: var(--text-muted); margin:0;"><?php echo htmlspecialchars($orig['content']); ?></p>
                        </div>
                    <?php endif; endif; ?>

                    <!-- Stats Bar -->
                    <div class="post-stats">
                        <span id="like-count-<?php echo $post_id; ?>" style="display: flex; align-items: center; gap: 6px;">
                            <div style="background: var(--uiu-orange); border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                <svg width="10" height="10" fill="white" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path></svg>
                            </div>
                            <?php echo $like_count; ?>
                        </span>
                        <span id="comment-count-<?php echo $post_id; ?>" style="cursor:pointer;" onclick="toggleComments(<?php echo $post_id; ?>)"><?php echo $comment_count; ?> Comments</span>
                    </div>

                    <div class="post-actions">
                        <button class="action-btn <?php echo $is_liked ? 'active' : ''; ?>" onclick="toggleLike(<?php echo $post_id; ?>, this)">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg> Like
                        </button>
                        <button class="action-btn" onclick="toggleComments(<?php echo $post_id; ?>)">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg> Comment
                        </button>
                        <button class="action-btn" onclick="openShareModal(<?php echo $post_id; ?>)">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg> Share
                        </button>
                        <button class="action-btn <?php echo $is_saved ? 'active' : ''; ?>" onclick="toggleSave(<?php echo $post_id; ?>, this)">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg> Save
                        </button>
                    </div>

                    <!-- COMMENTS SECTION -->
                    <div class="comments-section" id="comments-<?php echo $post_id; ?>" style="display:none;">
                        <div id="comment-list-<?php echo $post_id; ?>" style="max-height: 400px; overflow-y: auto;">
                            <?php 
                            $parents = $conn->query("SELECT c.*, u.full_name, u.profile_pic FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = $post_id AND c.parent_id IS NULL ORDER BY c.created_at ASC");
                            while($c = $parents->fetch_assoc()): 
                                $c_initial = strtoupper($c['full_name'][0]);
                                $c_id = $c['id'];
                                $c_liked = $conn->query("SELECT id FROM comment_likes WHERE user_id = $user_id AND comment_id = $c_id")->num_rows > 0;
                                $cl_count = $conn->query("SELECT COUNT(id) as cnt FROM comment_likes WHERE comment_id = $c_id")->fetch_assoc()['cnt'];
                            ?>
                            <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                                <a href="profile.php?id=<?php echo $c['user_id']; ?>" class="avatar-sm" style="width: 32px; height: 32px; font-size: 12px; background: var(--bg-light); border: 1px solid var(--border-light); overflow: hidden; text-decoration: none;">
                                    <?php if($c['profile_pic']): ?>
                                        <img src="<?php echo htmlspecialchars($c['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: echo $c_initial; endif; ?>
                                </a>
                                <div style="flex: 1;">
                                    <div style="background: var(--bg-light); padding: 8px 12px; border-radius: 16px; display: inline-block;">
                                        <a href="profile.php?id=<?php echo $c['user_id']; ?>" style="font-size: 13px; color: var(--text-main); font-weight: 600; text-decoration: none;"><?php echo htmlspecialchars($c['full_name']); ?></a>
                                        <p style="font-size: 14px; color: var(--text-main); margin-top: 2px;"><?php echo htmlspecialchars($c['comment_text']); ?></p>
                                    </div>
                                    <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-left: 12px; margin-top: 4px; display:flex; gap:15px; align-items: center;">
                                        <span style="cursor:pointer; <?php echo $c_liked ? 'color:var(--uiu-orange);' : ''; ?>" onclick="toggleCommentLike(<?php echo $c_id; ?>, this)">Like <span id="clike-count-<?php echo $c_id; ?>"><?php echo $cl_count > 0 ? "($cl_count)" : ""; ?></span></span>
                                        <span style="cursor:pointer;" onclick="focusReply(<?php echo $post_id; ?>, <?php echo $c_id; ?>, '<?php echo addslashes($c['full_name']); ?>')">Reply</span>
                                        <span style="font-weight:400;"><?php echo date('M d', strtotime($c['created_at'])); ?></span>
                                    </div>
                                    
                                    <div id="replies-for-<?php echo $c_id; ?>">
                                        <?php 
                                        $replies = $conn->query("SELECT c.*, u.full_name, u.profile_pic FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = $post_id AND c.parent_id = $c_id ORDER BY c.created_at ASC");
                                        while($r = $replies->fetch_assoc()): 
                                            $r_initial = strtoupper($r['full_name'][0]);
                                            $r_id = $r['id'];
                                            $r_liked = $conn->query("SELECT id FROM comment_likes WHERE user_id = $user_id AND comment_id = $r_id")->num_rows > 0;
                                            $rl_count = $conn->query("SELECT COUNT(id) as cnt FROM comment_likes WHERE comment_id = $r_id")->fetch_assoc()['cnt'];
                                        ?>
                                        <div style="display: flex; gap: 10px; margin-top: 8px; margin-left: 10px;">
                                            <a href="profile.php?id=<?php echo $r['user_id']; ?>" class="avatar-sm" style="width: 24px; height: 24px; font-size: 10px; background: var(--bg-light); border: 1px solid var(--border-light); overflow: hidden; text-decoration: none;">
                                                <?php if($r['profile_pic']): ?>
                                                    <img src="<?php echo htmlspecialchars($r['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                                <?php else: echo $r_initial; endif; ?>
                                            </a>
                                            <div>
                                                <div style="background: var(--bg-light); padding: 8px 12px; border-radius: 16px; display: inline-block;">
                                                    <a href="profile.php?id=<?php echo $r['user_id']; ?>" style="font-size: 12px; color: var(--text-main); font-weight: 600; text-decoration: none;"><?php echo htmlspecialchars($r['full_name']); ?></a>
                                                    <p style="font-size: 13px; color: var(--text-main); margin-top: 2px;"><?php echo htmlspecialchars($r['comment_text']); ?></p>
                                                </div>
                                                <div style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-left: 12px; margin-top: 4px; display:flex; gap:12px; align-items:center;">
                                                    <span style="cursor:pointer; <?php echo $r_liked ? 'color:var(--uiu-orange);' : ''; ?>" onclick="toggleCommentLike(<?php echo $r_id; ?>, this)">Like <span id="clike-count-<?php echo $r_id; ?>"><?php echo $rl_count > 0 ? "($rl_count)" : ""; ?></span></span>
                                                    <span style="cursor:pointer;" onclick="focusReply(<?php echo $post_id; ?>, <?php echo $c_id; ?>, '<?php echo addslashes($r['full_name']); ?>')">Reply</span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="comment-input-area">
                            <div class="avatar-sm" style="width: 32px; height: 32px; font-size: 12px; background: var(--uiu-orange-light); color: var(--uiu-orange); overflow: hidden;">
                                <?php if($current_profile_pic): ?>
                                    <img src="<?php echo htmlspecialchars($current_profile_pic); ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: echo $current_initials; endif; ?>
                            </div>
                            <div style="flex:1;">
                                <input type="hidden" id="reply-to-<?php echo $post_id; ?>" value="">
                                <input type="text" id="comment-input-<?php echo $post_id; ?>" class="comment-input" style="width:100%;" placeholder="Write a comment... (Press Enter)" onkeypress="handleCommentSubmit(event, <?php echo $post_id; ?>)">
                                <div id="replying-text-<?php echo $post_id; ?>" class="replying-indicator">
                                    Replying to <strong id="reply-name-<?php echo $post_id; ?>"></strong> 
                                    <span class="cancel-reply" onclick="cancelReply(<?php echo $post_id; ?>)">Cancel</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div style="text-align:center; padding: 40px; color: var(--text-muted);">No posts found. Be the first to share!</div>';
        }
        ?>
    </div>

    <div class="sidebar-column">
        <!-- Announcements -->
        <div class="announcement-card">
            <h4 style="font-size: 15px; font-weight: 600; margin-bottom: 12px; color: #0369A1; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg> 
                Campus Announcements
            </h4>
            <?php
            $ann_sql = "SELECT p.content, p.created_at, u.full_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.post_type = 'Announcement' ORDER BY p.created_at DESC LIMIT 2";
            $ann_res = $conn->query($ann_sql);
            if ($ann_res && $ann_res->num_rows > 0) {
                while($ann = $ann_res->fetch_assoc()) {
                    echo '<div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(186,230,253,0.5);">
                            <p style="font-size: 13px; color: #0C4A6E; font-weight: 500; margin-bottom: 4px;">'.htmlspecialchars($ann['content']).'</p>
                            <small style="color: #0284C7; font-size: 11px;">By '.htmlspecialchars($ann['full_name']).' • '.date('M d', strtotime($ann['created_at'])).'</small>
                          </div>';
                }
            } else {
                echo '<div style="text-align:center; padding: 20px 0; opacity:0.8;">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: #0284C7; margin-bottom:8px; display:block; margin: 0 auto;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <p style="font-size: 13px; color: #0284C7;">No active announcements.</p>
                      </div>';
            }
            ?>
        </div>

        <!-- Schedule -->
        <div class="card" style="margin-bottom: 24px;">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px;">My Classes</h4>
            <div style="padding-left: 10px; border-left: 2px solid var(--border-light);">
                <?php
                if ($user_role === 'Student' && $is_setup_complete) {
                    // TODAY
                    $routine_sql = "SELECT c.course_code, c.course_name, c.room_no, c.start_time FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = $user_id AND c.day_of_week = '$today_day' ORDER BY c.start_time ASC";
                    $res = $conn->query($routine_sql);
                    if ($res && $res->num_rows > 0) {
                        echo '<strong style="font-size: 12px; color: var(--uiu-orange); text-transform: uppercase; margin-bottom: 8px; display:block;">Today</strong>';
                        while($class = $res->fetch_assoc()) {
                            echo '<div class="timeline-item">
                                    <p style="font-size: 14px; font-weight: 600; color: var(--text-main);">'.$class['course_code'].' - '.$class['course_name'].'</p>
                                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">'.date('h:i A', strtotime($class['start_time'])).' • Room '.$class['room_no'].'</p>
                                  </div>';
                        }
                    } else { echo '<p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px;">No classes today.</p>'; }
                    
                    // TOMORROW
                    $routine_sql_tmr = "SELECT c.course_code, c.course_name, c.room_no, c.start_time FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = $user_id AND c.day_of_week = '$tomorrow_day' ORDER BY c.start_time ASC";
                    $res_tmr = $conn->query($routine_sql_tmr);
                    if ($res_tmr && $res_tmr->num_rows > 0) {
                        echo '<strong style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-top: 10px; margin-bottom: 8px; display:block;">Upcoming (Tomorrow)</strong>';
                        while($class = $res_tmr->fetch_assoc()) {
                            echo '<div class="timeline-item" style="opacity: 0.8;">
                                    <p style="font-size: 14px; font-weight: 600; color: var(--text-main);">'.$class['course_code'].' - '.$class['course_name'].'</p>
                                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">'.date('h:i A', strtotime($class['start_time'])).' • Room '.$class['room_no'].'</p>
                                  </div>';
                        }
                    }
                } else { echo '<p style="font-size: 12px; color: var(--text-muted);">Please sync routine.</p>'; }
                ?>
            </div>
        </div>

        <!-- Assignments -->
        <div class="card" style="margin-bottom: 24px;">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px;">Pending Assignments</h4>
            <?php
            if ($user_role === 'Student' && $is_setup_complete) {
                $tasks_sql = "SELECT t.task_title, t.deadline, c.course_code FROM tasks t JOIN enrollments e ON t.course_id = e.course_id JOIN courses c ON t.course_id = c.id WHERE e.student_id = $user_id AND t.deadline >= CURDATE() ORDER BY t.deadline ASC LIMIT 3";
                $tasks_result = $conn->query($tasks_sql);
                if ($tasks_result && $tasks_result->num_rows > 0) {
                    while($task = $tasks_result->fetch_assoc()) {
                        echo '<div style="padding: 12px; background: #FEF2F2; border: 1px solid #FEE2E2; border-radius: 8px; margin-bottom: 10px;">
                                <strong style="font-size: 12px; color: #EF4444;">'.$task['course_code'].'</strong>
                                <p style="font-size: 13px; color: var(--text-main); font-weight: 500; margin: 4px 0;">'.$task['task_title'].'</p>
                                <small style="color: var(--text-muted); font-size: 11px;">Due: '.date('M d, g:i A', strtotime($task['deadline'])).'</small>
                              </div>';
                    }
                } else { 
                    echo '<div style="text-align:center; padding: 10px;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #10B981; margin-bottom:5px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p style="font-size: 12px; color: var(--text-muted);">All caught up!</p>
                          </div>'; 
                }
            } else { echo '<p style="font-size: 12px; color: var(--text-muted); text-align: center;">Sync routine to view tasks.</p>'; }
            ?>
        </div>

        <!-- Marketplace -->
        <div class="card" style="margin-bottom: 24px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h4 style="font-size: 16px; font-weight: 600; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--uiu-orange);"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg> 
                    Marketplace
                </h4>
                <a href="marketplace.php" style="font-size: 13px; color: var(--uiu-orange); text-decoration: none; font-weight: 600;">View All</a>
            </div>
            
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php
                $market_sql = "SELECT m.*, u.full_name FROM marketplace m JOIN users u ON m.seller_id = u.id WHERE m.admin_approval_status = 'approved' ORDER BY m.created_at DESC LIMIT 3";
                $market_res = $conn->query($market_sql);

                if ($market_res && $market_res->num_rows > 0) {
                    while($item = $market_res->fetch_assoc()) {
                        ?>
                        <a href="marketplace.php#item-<?php echo $item['id']; ?>" style="display:flex; align-items:center; gap:12px; padding:12px; border:1px solid var(--border-light); border-radius:10px; background:var(--bg-light); text-decoration:none; transition:all 0.2s ease;" onmouseover="this.style.borderColor='var(--uiu-orange-light)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';" onmouseout="this.style.borderColor='var(--border-light)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <div style="width:48px; height:48px; background:#e2e8f0; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden;">
                                <?php if($item['image_path']): ?>
                                    <img src="<?php echo $item['image_path']; ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#64748b;"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <?php endif; ?>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <strong style="font-size:14px; color:var(--text-main); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($item['item_title']); ?></strong>
                                <span style="font-size:13px; color:var(--uiu-orange); font-weight:700;">৳<?php echo number_format($item['price']); ?></span>
                                <small style="color:var(--text-muted); font-size:12px; margin-left:6px;">• <?php echo htmlspecialchars($item['item_condition']); ?></small>
                            </div>
                        </a>
                        <?php
                    }
                } else {
                    echo '<div style="text-align:center; padding:15px; color:var(--text-muted); font-size:13px;">No items listed currently.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</main>

<!-- SHARE -->
<div class="modal-overlay" id="shareModal">
    <div class="modal-content" style="max-width: 500px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Share Post</h3>
            <button style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('shareModal').style.display='none'">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <div class="avatar-sm" style="background: var(--uiu-orange-light); color: var(--uiu-orange); overflow:hidden;">
                <?php if($current_profile_pic): ?>
                    <img src="<?php echo htmlspecialchars($current_profile_pic); ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: echo $current_initials; endif; ?>
            </div>
            <strong style="font-size: 14px; margin-top: 10px;"><?php echo $current_user_name; ?></strong>
        </div>
        <textarea id="shareText" class="composer-input" style="width:100%; min-height: 80px; padding:0; margin-bottom:15px;" placeholder="Say something about this..."></textarea>
        <input type="hidden" id="sharePostId">
        <button class="btn-primary" style="width:100%; justify-content: center;" onclick="submitShare()">Share Now</button>
    </div>
</div>

<!-- EDIT POST -->
<div class="modal-overlay" id="editModal">
    <div class="modal-content" style="max-width: 500px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Edit Post</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('editModal').style.display='none'">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <textarea id="editText" class="composer-input" style="width:100%; min-height: 100px; padding:0; margin-bottom:15px; border: 1px solid var(--border-light); padding: 10px; border-radius: 8px;"></textarea>
        <input type="hidden" id="editPostId">
        <button class="btn-primary" style="width:100%; justify-content: center;" onclick="submitEdit()">Save Changes</button>
    </div>
</div>

<!-- EDIT HISTORY -->
<div class="modal-overlay" id="historyModal">
    <div class="modal-content" style="max-width: 500px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Edit History</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('historyModal').style.display='none'">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="historyContent" style="max-height: 300px; overflow-y: auto;"></div>
    </div>
</div>

<div id="customDeleteModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 30px;">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: #EF4444; margin-bottom: 15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        <h3 style="font-size:18px; color: var(--text-main); margin-bottom: 10px;">Delete Post?</h3>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 25px;">This action cannot be undone.</p>
        <div style="display:flex; gap:10px; justify-content: center;">
            <button onclick="document.getElementById('customDeleteModal').style.display='none'" style="padding: 10px 24px; border: 1px solid var(--border-light); background: #fff; border-radius: 8px; cursor: pointer; font-weight: 600;">Cancel</button>
            <button id="confirmDeleteBtn" style="padding: 10px 24px; border: none; background: #EF4444; color: white; border-radius: 8px; cursor: pointer; font-weight: 600;">Delete</button>
        </div>
    </div>
</div>

<script src="assets/main.js"></script>
</body>
</html>