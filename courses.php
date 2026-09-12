<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$current_page = 'courses.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_material'])) {
    $course_id = intval($_POST['course_id']);
    $title = $conn->real_escape_string($_POST['material_title']);
    $category = $conn->real_escape_string($_POST['material_category']);
    
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $target_dir = "uploads/materials/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . '_' . basename($_FILES["material_file"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["material_file"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO course_materials (course_id, uploader_id, title, category, file_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $course_id, $user_id, $title, $category, $target_file);
            $stmt->execute();
        }
    }
    header("Location: courses.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax_action'] == 'search_courses') {
        $query = $conn->real_escape_string($_POST['query']);
        $sql = "SELECT id, course_code, course_name FROM courses WHERE course_code LIKE '%$query%' OR course_name LIKE '%$query%' GROUP BY course_code LIMIT 10";
        $res = $conn->query($sql);
        $html = '';
        if ($res->num_rows > 0) {
            while ($c = $res->fetch_assoc()) {
                $html .= '<div class="course-item" onclick="loadMaterials('.$c['id'].', \''.$c['course_code'].'\')">
                            <strong style="font-size: 15px; color: var(--text-main); display: block;">'.htmlspecialchars($c['course_code']).'</strong>
                            <div style="font-size: 13px; color: var(--text-muted);">'.htmlspecialchars($c['course_name']).'</div>
                          </div>';
            }
        } else { $html = '<div style="padding: 20px; text-align: center; color: var(--text-muted);">No courses found.</div>'; }
        echo json_encode(['html' => $html]); exit();
    }
    
    if ($_POST['ajax_action'] == 'load_materials') {
        $course_id = intval($_POST['course_id']);
        $c_code_res = $conn->query("SELECT course_code, course_name FROM courses WHERE id = $course_id LIMIT 1");
        $c_data = $c_code_res->fetch_assoc();
        $code = $c_data['course_code'];
        $name = $c_data['course_name'];

        $sql = "SELECT m.*, u.full_name FROM course_materials m 
                JOIN courses c ON m.course_id = c.id 
                JOIN users u ON m.uploader_id = u.id 
                WHERE c.course_code = '$code' ORDER BY m.uploaded_at DESC";
        $res = $conn->query($sql);
        
        $categories = ['Mid Question' => '', 'Final Question' => '', 'CT Question' => '', 'Note' => '', 'Book' => ''];
        $counts = ['Mid Question' => 0, 'Final Question' => 0, 'CT Question' => 0, 'Note' => 0, 'Book' => 0];
        
        if ($res->num_rows > 0) {
            while ($m = $res->fetch_assoc()) {
                $cat = $m['category'];
                $counts[$cat]++;
                $categories[$cat] .= '
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #fff; border: 1px solid var(--border-light); border-radius: 8px; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; background: var(--uiu-orange-light); color: var(--uiu-orange); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-file-lines" style="font-size: 20px;"></i>
                            </div>
                            <div>
                                <strong style="font-size: 14px; color: var(--text-main); display: block;">'.htmlspecialchars($m['title']).'</strong>
                                <span style="font-size: 11px; color: var(--text-muted);">Uploaded by '.htmlspecialchars($m['full_name']).' • '.date('M d, Y', strtotime($m['uploaded_at'])).'</span>
                            </div>
                        </div>
                        <a href="'.htmlspecialchars($m['file_path']).'" download class="btn-outline" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">Download</a>
                    </div>';
            }
        }
        
        $html = '<div style="padding: 24px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="font-size: 24px; color: var(--text-main); margin-bottom: 4px;">'.$code.'</h2>
                        <p style="font-size: 14px; color: var(--text-muted);">'.$name.'</p>
                    </div>
                    <button class="btn-primary" onclick="openUploadModal('.$course_id.', \''.$code.'\')">
                        <i class="fa-solid fa-upload" style="vertical-align: middle; margin-right: 4px; font-size: 16px;"></i> Upload Material
                    </button>
                 </div>
                 
                 <div style="padding: 24px;">
                    <div style="display: flex; gap: 10px; border-bottom: 1px solid var(--border-light); padding-bottom: 10px; margin-bottom: 20px; overflow-x: auto;">
                        <button class="cat-tab active" onclick="switchCat(\'Mid Question\', this)">Mid Questions ('.$counts['Mid Question'].')</button>
                        <button class="cat-tab" onclick="switchCat(\'Final Question\', this)">Final Questions ('.$counts['Final Question'].')</button>
                        <button class="cat-tab" onclick="switchCat(\'CT Question\', this)">CT Questions ('.$counts['CT Question'].')</button>
                        <button class="cat-tab" onclick="switchCat(\'Note\', this)">Notes ('.$counts['Note'].')</button>
                        <button class="cat-tab" onclick="switchCat(\'Book\', this)">Books ('.$counts['Book'].')</button>
                    </div>
                    
                    <div id="cat-Mid Question" class="cat-content active">'.($categories['Mid Question'] ?: '<p style="color:var(--text-muted); font-size:13px;">No mid questions available. Be the first to upload!</p>').'</div>
                    <div id="cat-Final Question" class="cat-content" style="display:none;">'.($categories['Final Question'] ?: '<p style="color:var(--text-muted); font-size:13px;">No final questions available.</p>').'</div>
                    <div id="cat-CT Question" class="cat-content" style="display:none;">'.($categories['CT Question'] ?: '<p style="color:var(--text-muted); font-size:13px;">No CT questions available.</p>').'</div>
                    <div id="cat-Note" class="cat-content" style="display:none;">'.($categories['Note'] ?: '<p style="color:var(--text-muted); font-size:13px;">No notes available.</p>').'</div>
                    <div id="cat-Book" class="cat-content" style="display:none;">'.($categories['Book'] ?: '<p style="color:var(--text-muted); font-size:13px;">No books available.</p>').'</div>
                 </div>';
                 
        echo json_encode(['status' => 'success', 'html' => $html]); exit();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/courses.css">

<div class="courses-container">
    
    <div class="course-list-panel">
        <div class="course-list-header">
            <h2 style="font-size: 20px; color: var(--text-main); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-book-open" style="color: var(--uiu-orange); font-size: 24px;"></i>
                Material Hub
            </h2>
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
                <input type="text" id="courseSearch" placeholder="Search any course (e.g. CSE215)" style="width: 100%; padding: 10px 12px 10px 36px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 13px; outline: none; background: #fff; box-sizing: border-box;" onkeyup="searchCourses(this.value)">
            </div>
        </div>
        
        <div id="courseList" style="overflow-y: auto; flex: 1;">
            <?php
            $courses_sql = "SELECT id, course_code, course_name FROM courses GROUP BY course_code ORDER BY course_code ASC LIMIT 15";
            $courses_res = $conn->query($courses_sql);
            $first_course_id = null;
            $first_course_code = null;

            if ($courses_res && $courses_res->num_rows > 0) {
                while($c = $courses_res->fetch_assoc()) {
                    if ($first_course_id === null) { $first_course_id = $c['id']; $first_course_code = $c['course_code']; }
                    echo '<div class="course-item" onclick="loadMaterials('.$c['id'].', \''.$c['course_code'].'\')">
                            <strong style="font-size: 15px; color: var(--text-main); display: block; margin-bottom: 4px;">'.htmlspecialchars($c['course_code']).'</strong>
                            <div style="font-size: 13px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'.htmlspecialchars($c['course_name']).'</div>
                          </div>';
                }
            }
            ?>
        </div>
    </div>

    <div class="course-content-panel" id="courseContent">
        <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--text-muted);">
            <i class="fa-solid fa-book-open" style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;"></i>
            <h3 style="font-size:18px; font-weight:600; color:var(--text-main); margin-bottom:10px;">Select a Course</h3>
            <p style="font-size:14px;">Browse or upload materials for any course.</p>
        </div>
    </div>
</div>

<div class="modal-overlay" id="uploadModal">
    <div class="modal-content" style="max-width: 450px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Contribute Material</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('uploadModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 20px;"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="upload_material" value="1">
            <input type="hidden" name="course_id" id="uploadCourseId">
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Course</label>
            <input type="text" id="uploadCourseCode" class="poll-input" disabled style="background: var(--bg-light); color: var(--text-main); font-weight: 600;">

            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Material Category *</label>
            <select name="material_category" class="poll-input" required>
                <option value="Mid Question">Mid Question</option>
                <option value="Final Question">Final Question</option>
                <option value="CT Question">CT Question</option>
                <option value="Note">Class Note</option>
                <option value="Book">Reference Book</option>
            </select>

            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Title *</label>
            <input type="text" name="material_title" class="poll-input" placeholder="e.g., Fall 2025 Midterm Question" required>

            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">File (PDF, DOCX, ZIP) *</label>
            <input type="file" name="material_file" class="poll-input" style="padding: 7px 12px;" required>

            <button type="submit" class="btn-primary" style="width:100%; margin-top: 10px;">Upload & Share</button>
        </form>
    </div>
</div>

<script>
    function searchCourses(query) {
        if(query.length < 2 && query.length > 0) return;
        let fd = new FormData();
        fd.append('ajax_action', 'search_courses');
        fd.append('query', query);
        fetch('courses.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            document.getElementById('courseList').innerHTML = data.html;
        });
    }

    function loadMaterials(courseId, courseCode) {
        document.getElementById('courseContent').innerHTML = '<div style="flex:1; display:flex; align-items:center; justify-content:center; color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin" style="font-size: 40px;"></i></div>';
        
        let fd = new FormData();
        fd.append('ajax_action', 'load_materials');
        fd.append('course_id', courseId);
        
        fetch('courses.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            if(data.status === 'success') { document.getElementById('courseContent').innerHTML = data.html; }
        });
    }

    function switchCat(catName, btnElement) {
        document.querySelectorAll('.cat-tab').forEach(el => el.classList.remove('active'));
        btnElement.classList.add('active');
        document.querySelectorAll('.cat-content').forEach(el => el.style.display = 'none');
        document.getElementById('cat-' + catName).style.display = 'block';
    }

    function openUploadModal(courseId, courseCode) {
        document.getElementById('uploadCourseId').value = courseId;
        document.getElementById('uploadCourseCode').value = courseCode;
        document.getElementById('uploadModal').style.display = 'flex';
    }

    <?php if($first_course_id): ?>
        window.onload = function() { loadMaterials(<?php echo $first_course_id; ?>, '<?php echo $first_course_code; ?>'); };
    <?php endif; ?>
</script>

<script src="assets/main.js"></script>
</body>
</html>