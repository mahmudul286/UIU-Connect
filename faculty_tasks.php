<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Faculty') { 
    header("Location: dashboard.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$current_page = 'faculty_tasks.php';

// Auto-add file_path column if it doesn't exist to prevent errors
$conn->query("ALTER TABLE tasks ADD COLUMN IF NOT EXISTS file_path VARCHAR(255) DEFAULT NULL");

// Add Task Action with File Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $course_id = intval($_POST['course_id']);
    $task_title = $conn->real_escape_string($_POST['task_title']);
    $deadline = $conn->real_escape_string($_POST['deadline']);
    $file_path = NULL;

    // Secure File Upload Logic
    if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] == 0) {
        $target_dir = "uploads/tasks/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["task_file"]["name"], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'jpg', 'png'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $file_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES["task_file"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
            }
        }
    }
    
    // Insert new task
    $stmt = $conn->prepare("INSERT INTO tasks (course_id, faculty_id, task_title, deadline, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $course_id, $user_id, $task_title, $deadline, $file_path);
    $stmt->execute();
    
    header("Location: faculty_tasks.php?msg=task_added");
    exit();
}

// Delete Task
if (isset($_GET['delete_task'])) {
    $task_id = intval($_GET['delete_task']);
    
    // Remove the file if exists
    $check = $conn->query("SELECT file_path FROM tasks WHERE id = $task_id AND faculty_id = $user_id");
    if ($check->num_rows > 0) {
        $file = $check->fetch_assoc()['file_path'];
        if ($file && file_exists($file)) unlink($file);
    }
    
    $conn->query("DELETE FROM tasks WHERE id = $task_id AND faculty_id = $user_id");
    header("Location: faculty_tasks.php?msg=task_deleted");
    exit();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<style>
    .faculty-layout { flex: 1; padding: 24px; display: flex; gap: 24px; max-width: 1200px; margin: 0 auto; }
    .main-column { flex: 1; max-width: 750px; }
    .side-column { width: 340px; flex-shrink: 0; }
</style>

<div id="toastMessage" class="toast">
    <i class="fa-solid fa-check" style="color: #10B981; font-size: 20px;"></i>
    <span id="toastText">Action successful</span>
</div>

<main class="faculty-layout">
    
    <div class="main-column">
        <div class="card" style="margin-bottom: 24px; padding: 20px; background: linear-gradient(135deg, #0f172a 0%, #334155 100%); color: white;">
            <h1 style="font-size: 22px; margin-bottom: 8px;">Task & Assignment Management</h1>
            <p style="font-size: 14px; opacity: 0.8;">Assign deadlines and upload necessary files for the courses assigned to you by the Admin. Students will see these directly on their dashboard.</p>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; color: var(--text-main);">Active Assignments & Tasks</h3>
                <button class="btn-primary" onclick="document.getElementById('taskModal').style.display='flex'">
                    <i class="fa-solid fa-plus"></i> Create Task
                </button>
            </div>

            <?php
            // Fetch tasks created by this faculty
            $task_sql = "SELECT t.*, c.course_code, c.course_name FROM tasks t JOIN courses c ON t.course_id = c.id WHERE t.faculty_id = $user_id ORDER BY t.deadline ASC";
            $task_res = $conn->query($task_sql);

            if ($task_res && $task_res->num_rows > 0) {
                while($task = $task_res->fetch_assoc()) {
                    $is_past = (strtotime($task['deadline']) < time());
                    $status_color = $is_past ? '#64748b' : '#EF4444';
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 15px; border: 1px solid var(--border-light); border-radius: 8px; margin-bottom: 12px; background: #fff;">
                        <div>
                            <span style="font-size: 12px; background: var(--bg-light); padding: 4px 8px; border-radius: 6px; font-weight: 600; color: var(--uiu-orange);"><?php echo htmlspecialchars($task['course_code']); ?></span>
                            <h4 style="font-size: 15px; color: var(--text-main); margin: 8px 0 4px 0;"><?php echo htmlspecialchars($task['task_title']); ?></h4>
                            <div style="font-size: 12px; color: <?php echo $status_color; ?>; font-weight: 500; margin-bottom: 8px;">
                                <i class="fa-regular fa-clock"></i> Deadline: <?php echo date('M d, Y - g:i A', strtotime($task['deadline'])); ?>
                                <?php if($is_past) echo " (Expired)"; ?>
                            </div>
                            <?php if($task['file_path']): ?>
                                <a href="<?php echo htmlspecialchars($task['file_path']); ?>" download class="btn-outline" style="font-size: 11px; padding: 4px 10px;">
                                    <i class="fa-solid fa-file-arrow-down"></i> Download Attached File
                                </a>
                            <?php endif; ?>
                        </div>
                        <a href="faculty_tasks.php?delete_task=<?php echo $task['id']; ?>" style="color: #EF4444; background: #FEE2E2; padding: 8px 12px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 600;" onclick="return confirm('Are you sure you want to delete this task?');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                    <?php
                }
            } else {
                echo '<div style="text-align:center; padding: 40px; color: var(--text-muted); background: var(--bg-light); border-radius: 8px;">No tasks assigned yet.</div>';
            }
            ?>
        </div>
    </div>

    <div class="side-column">
        <!-- Assigned Courses Widget -->
        <div class="card" style="margin-bottom: 24px;">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-book" style="color: var(--uiu-orange); font-size: 18px;"></i> Assigned Courses
            </h4>
            
            <div style="margin-bottom: 10px;">
                <?php
                $my_courses = $conn->query("SELECT * FROM courses WHERE faculty_id = $user_id");
                if ($my_courses->num_rows > 0) {
                    while($mc = $my_courses->fetch_assoc()) {
                        echo '<div style="padding: 10px 12px; border-left: 3px solid var(--uiu-orange); background: var(--bg-light); border-radius: 0 6px 6px 0; margin-bottom: 8px; font-size: 13px; font-weight: 600;">
                                '.htmlspecialchars($mc['course_code']).' - '.htmlspecialchars($mc['course_name']).'
                              </div>';
                    }
                } else {
                    echo '<p style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 20px 0;">No courses assigned yet. Contact Admin.</p>';
                }
                ?>
            </div>
            <p style="font-size: 11px; color: var(--text-muted); text-align: center; font-style: italic;">Courses are managed and assigned by the Administration.</p>
        </div>
    </div>
</main>

<!-- Create Task Modal -->
<div class="modal-overlay" id="taskModal">
    <div class="modal-content" style="max-width: 450px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Create Assignment/Task</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('taskModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 20px;"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add_task" value="1">
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Select Assigned Course *</label>
            <select name="course_id" class="poll-input" required>
                <?php
                $my_courses->data_seek(0); // Reset pointer
                if ($my_courses->num_rows > 0) {
                    while($mc = $my_courses->fetch_assoc()) {
                        echo '<option value="'.$mc['id'].'">'.htmlspecialchars($mc['course_code']).' - '.htmlspecialchars($mc['course_name']).'</option>';
                    }
                } else {
                    echo '<option value="">No courses assigned</option>';
                }
                ?>
            </select>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Task Title *</label>
            <input type="text" name="task_title" class="poll-input" placeholder="e.g. Assignment 1 - Algorithm Analysis" required>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Deadline *</label>
            <input type="datetime-local" name="deadline" class="poll-input" required>

            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Attach File (Optional)</label>
            <input type="file" name="task_file" class="poll-input" style="padding: 7px 12px;">
            <small style="color: var(--text-muted); font-size: 11px; display: block; margin-top: -5px; margin-bottom: 15px;">PDF, DOCX, ZIP, or Image files.</small>

            <button type="submit" class="btn-primary" style="width:100%;">Assign Task</button>
        </form>
    </div>
</div>

<script>
    function showToast(message) {
        let toast = document.getElementById("toastMessage");
        document.getElementById("toastText").innerText = message;
        toast.className = "toast show";
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('msg')) {
        let msg = urlParams.get('msg');
        if (msg === 'task_added') showToast("Task successfully assigned to students.");
        if (msg === 'task_deleted') showToast("Task deleted successfully.");
        window.history.replaceState(null, null, window.location.pathname);
    }
</script>

<script src="assets/main.js"></script>
</body>
</html>