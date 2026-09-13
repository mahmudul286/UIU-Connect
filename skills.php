<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$current_page = 'skills.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    if ($action == 'add_skill') {
        $skill = $conn->real_escape_string(trim($_POST['skill_name']));
        if (!empty($skill)) {
            $check = $conn->query("SELECT id FROM user_skills WHERE user_id = $user_id AND LOWER(skill_name) = LOWER('$skill')");
            if ($check->num_rows == 0) {
                $conn->query("INSERT INTO user_skills (user_id, skill_name) VALUES ($user_id, '$skill')");
                $new_id = $conn->insert_id;
                $html = '<span class="skill-tag" id="skill-'.$new_id.'">
                            '.htmlspecialchars($skill).'
                            <button onclick="removeSkill('.$new_id.')"><i class="fa-solid fa-xmark" style="font-size: 12px;"></i></button>
                         </span>';
                echo json_encode(['status' => 'success', 'html' => $html]); exit();
            }
        }
        echo json_encode(['status' => 'error', 'message' => 'Skill already exists.']); exit();
    }

    if ($action == 'remove_skill') {
        $skill_id = intval($_POST['skill_id']);
        $conn->query("DELETE FROM user_skills WHERE id = $skill_id AND user_id = $user_id");
        echo json_encode(['status' => 'success']); exit();
    }

    if ($action == 'toggle_project_like') {
        $proj_id = intval($_POST['project_id']);
        $check = $conn->query("SELECT id FROM project_likes WHERE user_id = $user_id AND project_id = $proj_id");
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM project_likes WHERE user_id = $user_id AND project_id = $proj_id");
            $is_liked = false;
        } else {
            $conn->query("INSERT INTO project_likes (user_id, project_id) VALUES ($user_id, $proj_id)");
            $is_liked = true;
        }
        $count = $conn->query("SELECT COUNT(id) as c FROM project_likes WHERE project_id = $proj_id")->fetch_assoc()['c'];
        echo json_encode(['status' => 'success', 'is_liked' => $is_liked, 'count' => $count]); exit();
    }

    if ($action == 'delete_project') {
        $proj_id = intval($_POST['project_id']);
        $check = $conn->query("SELECT image_path FROM projects WHERE id = $proj_id AND user_id = $user_id");
        if ($check->num_rows > 0) {
            $img = $check->fetch_assoc()['image_path'];
            if ($img && file_exists($img)) unlink($img);
            $conn->query("DELETE FROM projects WHERE id = $proj_id");
            echo json_encode(['status' => 'success']);
        }
        exit();
    }

    if ($action == 'search_collaborator') {
        $query = $conn->real_escape_string($_POST['query']);
        
        $sql = "SELECT u.id, u.full_name, u.department, 
                       (SELECT GROUP_CONCAT(DISTINCT skill_name SEPARATOR ', ') FROM user_skills WHERE user_id = u.id) as all_skills,
                       (SELECT GROUP_CONCAT(DISTINCT tech_stack SEPARATOR ', ') FROM projects WHERE user_id = u.id) as all_techs
                FROM users u 
                WHERE EXISTS (SELECT 1 FROM user_skills WHERE user_id = u.id AND skill_name LIKE '%$query%')
                   OR EXISTS (SELECT 1 FROM projects WHERE user_id = u.id AND tech_stack LIKE '%$query%')
                LIMIT 5";
                
        $res = $conn->query($sql);
        $html = '';
        if ($res->num_rows > 0) {
            while ($u = $res->fetch_assoc()) {
                $initial = strtoupper($u['full_name'][0]);
                $is_me = ($u['id'] == $user_id) ? ' <span style="color:var(--uiu-orange); font-size:11px;">(You)</span>' : '';
                
                $display_match = $u['all_skills'] ? $u['all_skills'] : '';
                if ($u['all_techs']) {
                    $display_match .= $display_match ? ' | Built with: ' . $u['all_techs'] : 'Built with: ' . $u['all_techs'];
                }
                
                $html .= '<div style="display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid var(--border-light); border-radius:8px; margin-bottom:10px; background:#fff; transition:0.2s;" onmouseover="this.style.borderColor=\'var(--uiu-orange)\'" onmouseout="this.style.borderColor=\'var(--border-light)\'">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="avatar-sm" style="background:var(--bg-light); border:1px solid var(--border-light); color:var(--text-main); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">'.$initial.'</div>
                                <div style="overflow: hidden; max-width: 160px;">
                                    <strong style="font-size:14px; color:var(--text-main); display:block; white-space:nowrap; text-overflow:ellipsis;">'.htmlspecialchars($u['full_name']).$is_me.'</strong>
                                    <span style="font-size:11px; color:var(--text-muted); display:block; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">Matches: '.htmlspecialchars($display_match).'</span>
                                </div>
                            </div>';
                
                if ($u['id'] != $user_id) {
                    $html .= '<a href="messages.php?user='.$u['id'].'" class="btn-outline" style="padding:6px 12px; font-size:12px; text-decoration:none; flex-shrink:0;">Message</a>';
                }
                $html .= '</div>';
            }
        } else {
            $html = '<div style="padding:20px; text-align:center; color:var(--text-muted); font-size:13px;">
                        <i class="fa-solid fa-user-slash" style="margin-bottom:8px; opacity:0.5; font-size: 24px;"></i>
                        <br>No students or projects found with "'.$query.'"
                     </div>';
        }
        echo json_encode(['status' => 'success', 'html' => $html]); exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_project_id'])) {
    $proj_id = intval($_POST['edit_project_id']);
    $title = $conn->real_escape_string($_POST['project_title']);
    $desc = $conn->real_escape_string($_POST['project_description']);
    $tech = $conn->real_escape_string($_POST['tech_stack']);
    $repo = $conn->real_escape_string($_POST['repo_url']);
    $live = $conn->real_escape_string($_POST['live_url']);
    
    $check = $conn->query("SELECT id FROM projects WHERE id = $proj_id AND user_id = $user_id");
    if($check->num_rows > 0) {
        $conn->query("UPDATE projects SET title='$title', description='$desc', tech_stack='$tech', repo_url='$repo', live_url='$live' WHERE id=$proj_id");
    }
    header("Location: skills.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['project_title']) && !isset($_POST['edit_project_id'])) {
    $title = $conn->real_escape_string($_POST['project_title']);
    $desc = $conn->real_escape_string($_POST['project_description']);
    $tech = $conn->real_escape_string($_POST['tech_stack']);
    $repo = $conn->real_escape_string($_POST['repo_url']);
    $live = $conn->real_escape_string($_POST['live_url']);
    $image_path = NULL;

    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
        $target_dir = "uploads/projects/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . '_' . basename($_FILES["project_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_ext, ['jpg','jpeg','png','webp']) && move_uploaded_file($_FILES["project_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, tech_stack, repo_url, live_url, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $title, $desc, $tech, $repo, $live, $image_path);
    $stmt->execute();
    header("Location: skills.php"); exit();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/skills.css">

<div id="toastMessage" class="toast">
    <i class="fa-solid fa-check" style="color: #10B981; font-size: 20px;"></i>
    <span id="toastText">Action successful</span>
</div>

<main class="skills-layout">
    <div class="main-column">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 20px; background: #fff; border-radius: 12px; border: 1px solid var(--border-light); box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
            <div>
                <h2 style="font-size: 20px; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-laptop-code" style="color: var(--uiu-orange); font-size: 24px;"></i>
                    Project Showcase
                </h2>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Discover and get inspired by projects built by UIU students.</p>
            </div>
            <button class="btn-primary" onclick="document.getElementById('projectModal').style.display='flex'">
                <i class="fa-solid fa-plus" style="vertical-align: middle; margin-right: 4px; font-size: 16px;"></i> Add Project
            </button>
        </div>

        <?php
        $sql = "SELECT p.*, u.full_name, u.department, u.profile_pic FROM projects p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $proj_id = $row['id'];
                $like_count = $conn->query("SELECT COUNT(id) as c FROM project_likes WHERE project_id = $proj_id")->fetch_assoc()['c'];
                $is_liked = $conn->query("SELECT id FROM project_likes WHERE user_id = $user_id AND project_id = $proj_id")->num_rows > 0;
                ?>
                <div class="project-card" id="proj-card-<?php echo $proj_id; ?>">
                    <?php if($row['image_path']): ?>
                        <div class="project-img"><img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Project Image" loading="lazy"></div>
                    <?php else: ?>
                        <div class="project-img"><i class="fa-solid fa-display" style="color: var(--text-muted); opacity: 0.3; font-size: 48px;"></i></div>
                    <?php endif; ?>
                    
                    <div class="project-body">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <h3 class="project-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <a href="profile.php?id=<?php echo $row['user_id']; ?>" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                                    <div style="text-align: right;">
                                        <strong style="font-size: 13px; color: var(--text-main); display: block;"><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                        <span style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($row['department']); ?></span>
                                    </div>
                                    <div style="width: 32px; height: 32px; background: var(--bg-light); border: 1px solid var(--border-light); color: var(--text-main); border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">
                                        <?php if($row['profile_pic']): ?>
                                            <img src="<?php echo htmlspecialchars($row['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: echo strtoupper($row['full_name'][0]); endif; ?>
                                    </div>
                                </a>

                                <?php if($row['user_id'] == $user_id): ?>
                                <div style="position: relative;">
                                    <button style="background:none; border:none; color:var(--text-muted); cursor:pointer;" onclick="toggleProjMenu('proj-menu-<?php echo $proj_id; ?>')">
                                        <i class="fa-solid fa-ellipsis-vertical" style="font-size: 20px;"></i>
                                    </button>
                                    <div id="proj-menu-<?php echo $proj_id; ?>" class="post-options-menu" onmouseleave="this.style.display='none'">
                                        <button onclick="openEditProj(<?php echo $proj_id; ?>, '<?php echo addslashes($row['title']); ?>', '<?php echo addslashes($row['tech_stack']); ?>', '<?php echo addslashes(str_replace(["\r", "\n"], ["", "\\n"], $row['description'])); ?>', '<?php echo addslashes($row['repo_url']); ?>', '<?php echo addslashes($row['live_url']); ?>')">Edit</button>
                                        <button style="color:#EF4444;" onclick="deleteProject(<?php echo $proj_id; ?>)">Delete</button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if($row['tech_stack']): 
                            $techs = explode(',', $row['tech_stack']);
                            foreach($techs as $tech): ?>
                                <span class="tech-badge"><?php echo htmlspecialchars(trim($tech)); ?></span>
                        <?php endforeach; endif; ?>

                        <p class="project-desc"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        
                        <div class="project-links">
                            <?php if(!empty($row['repo_url'])): ?>
                                <a href="<?php echo htmlspecialchars($row['repo_url']); ?>" target="_blank" class="link-github">
                                    <i class="fa-brands fa-github" style="font-size: 16px;"></i> Repository
                                </a>
                            <?php endif; ?>
                            <?php if(!empty($row['live_url'])): ?>
                                <a href="<?php echo htmlspecialchars($row['live_url']); ?>" target="_blank" class="link-live">
                                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 16px;"></i> Live Demo
                                </a>
                            <?php endif; ?>
                            
                            <button class="proj-like-btn <?php echo $is_liked ? 'active' : ''; ?>" onclick="toggleProjectLike(<?php echo $proj_id; ?>, this)">
                                <i class="<?php echo $is_liked ? 'fa-solid' : 'fa-regular'; ?> fa-heart" style="font-size: 20px;"></i> 
                                <span id="proj-like-count-<?php echo $proj_id; ?>"><?php echo $like_count; ?></span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div style="text-align:center; padding: 40px; background: #fff; border-radius: 12px; border: 1px solid var(--border-light); color: var(--text-muted);">No projects uploaded yet. Showcase your work!</div>';
        }
        ?>
    </div>

    <div class="side-column">
        <div class="card" style="margin-bottom: 24px;">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bolt" style="color: var(--uiu-orange); font-size: 20px;"></i> My Skill Set
            </h4>
            <div id="mySkillsList" style="margin-bottom: 20px;">
                <?php
                $skills_sql = "SELECT id, skill_name FROM user_skills WHERE user_id = $user_id";
                $s_res = $conn->query($skills_sql);
                if ($s_res->num_rows > 0) {
                    while($s = $s_res->fetch_assoc()) {
                        echo '<span class="skill-tag" id="skill-'.$s['id'].'">
                                '.htmlspecialchars($s['skill_name']).'
                                <button onclick="removeSkill('.$s['id'].')"><i class="fa-solid fa-xmark" style="font-size: 12px;"></i></button>
                              </span>';
                    }
                } else {
                    echo '<p id="noSkillMsg" style="font-size: 13px; color: var(--text-muted);">No skills added yet.</p>';
                }
                ?>
            </div>
            <div style="margin-bottom: 15px;">
                <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Suggested</div>
                <span class="suggested-skill" onclick="addSkill('ReactJS')">+ ReactJS</span>
                <span class="suggested-skill" onclick="addSkill('Python')">+ Python</span>
                <span class="suggested-skill" onclick="addSkill('PHP')">+ PHP</span>
                <span class="suggested-skill" onclick="addSkill('UI/UX Design')">+ UI/UX Design</span>
                <span class="suggested-skill" onclick="addSkill('Machine Learning')">+ Machine Learning</span>
                <span class="suggested-skill" onclick="addSkill('Laravel')">+ Laravel</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <input type="text" id="newSkillInput" class="poll-input" style="margin-bottom: 0;" placeholder="Custom skill..." onkeypress="if(event.key === 'Enter') addSkill()">
                <button class="btn-primary" style="padding: 10px 16px;" onclick="addSkill()">Add</button>
            </div>
        </div>

        <div class="card">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-users-viewfinder" style="color: #3B82F6; font-size: 20px;"></i> Find Collaborators
            </h4>
            <div style="position: relative; margin-bottom: 16px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
                <input type="text" id="collabSearch" class="poll-input" style="margin-bottom: 0; padding-left: 36px;" placeholder="Search by skill (e.g. PHP)" onkeyup="searchTeammates(this.value)">
            </div>
            <div id="collabResults">
                <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:13px; background:var(--bg-light); border-radius:8px; border: 1px dashed var(--border-light);">
                    Type a skill to find project partners.
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal-overlay" id="projectModal">
    <div class="modal-content" style="max-width: 600px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Upload Project</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('projectModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 24px;"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div style="border: 2px dashed var(--border-light); border-radius: 8px; padding: 25px; text-align: center; cursor: pointer; margin-bottom: 15px; background: var(--bg-light); transition: 0.2s;" onclick="document.getElementById('projImage').click()" id="uploadBox">
                <i class="fa-solid fa-cloud-arrow-up" style="color: var(--text-muted); margin-bottom: 8px; font-size: 32px;"></i>
                <p style="font-size: 13px; color: var(--text-main); font-weight: 600;" id="uploadText">Upload Project Thumbnail</p>
                <p style="font-size: 12px; color: var(--text-muted);">Recommended: 1200x800px (JPG/PNG)</p>
                <input type="file" name="project_image" id="projImage" accept="image/*" style="display:none;" onchange="showProjectFileName(this)" required>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                <div style="flex: 2;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Project Title *</label>
                    <input type="text" name="project_title" class="poll-input" placeholder="e.g. UIU Connect" required>
                </div>
                <div style="flex: 3;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Tech Stack * (Comma separated)</label>
                    <input type="text" name="tech_stack" class="poll-input" placeholder="e.g. React, Node.js, MySQL" required>
                </div>
            </div>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Description *</label>
            <textarea name="project_description" class="poll-input" rows="4" placeholder="Briefly describe what this project does..." required></textarea>

            <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">GitHub/Repo URL</label>
                    <input type="url" name="repo_url" class="poll-input" placeholder="https://github.com/username/repo">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Live Demo URL</label>
                    <input type="url" name="live_url" class="poll-input" placeholder="https://myproject.com">
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; margin-top: 10px; padding: 12px; font-size: 15px;">Publish Project</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editProjModal">
    <div class="modal-content" style="max-width: 600px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Edit Project</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('editProjModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 24px;"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="edit_project_id" id="editProjId">
            <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Project Title</label>
            <input type="text" name="project_title" id="editProjTitle" class="poll-input" required>
            <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Tech Stack</label>
            <input type="text" name="tech_stack" id="editProjTech" class="poll-input" required>
            <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Description</label>
            <textarea name="project_description" id="editProjDesc" class="poll-input" rows="4" required></textarea>
            <div style="display: flex; gap: 15px;">
                <div style="flex:1;">
                    <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Repo URL</label>
                    <input type="url" name="repo_url" id="editProjRepo" class="poll-input" placeholder="Repo URL">
                </div>
                <div style="flex:1;">
                    <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Live Demo URL</label>
                    <input type="url" name="live_url" id="editProjLive" class="poll-input" placeholder="Live Demo URL">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; margin-top: 10px;">Save Changes</button>
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

    function toggleProjMenu(menuId) {
        let menu = document.getElementById(menuId);
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }

    function openEditProj(id, title, tech, desc, repo, live) {
        document.getElementById('editProjId').value = id;
        document.getElementById('editProjTitle').value = title;
        document.getElementById('editProjTech').value = tech;
        document.getElementById('editProjDesc').value = desc.replace(/\\n/g, '\n');
        document.getElementById('editProjRepo').value = repo;
        document.getElementById('editProjLive').value = live;
        document.getElementById('editProjModal').style.display = 'flex';
        document.getElementById('proj-menu-' + id).style.display = 'none';
    }

    function deleteProject(id) {
        if(confirm("Are you sure you want to delete this project?")) {
            let fd = new FormData(); fd.append('ajax_action', 'delete_project'); fd.append('project_id', id);
            fetch('skills.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
                if(data.status === 'success') {
                    let card = document.getElementById('proj-card-' + id);
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 300);
                    showToast("Project deleted successfully");
                }
            });
        }
    }

    function addSkill(skillName = null) {
        let skill = skillName ? skillName : document.getElementById('newSkillInput').value.trim();
        if(skill === '') return;
        
        let fd = new FormData();
        fd.append('ajax_action', 'add_skill');
        fd.append('skill_name', skill);
        
        fetch('skills.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            if(data.status === 'success') {
                document.getElementById('mySkillsList').insertAdjacentHTML('beforeend', data.html);
                let noMsg = document.getElementById('noSkillMsg');
                if(noMsg) noMsg.style.display = 'none';
                document.getElementById('newSkillInput').value = '';
                showToast("Skill added!");
            } else { showToast(data.message); }
        });
    }

    function removeSkill(skillId) {
        let fd = new FormData();
        fd.append('ajax_action', 'remove_skill');
        fd.append('skill_id', skillId);
        
        fetch('skills.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            if(data.status === 'success') {
                document.getElementById('skill-' + skillId).remove();
                showToast("Skill removed.");
            }
        });
    }

    function toggleProjectLike(projId, btn) {
        let fd = new FormData();
        fd.append('ajax_action', 'toggle_project_like');
        fd.append('project_id', projId);
        
        fetch('skills.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            if(data.status === 'success') {
                data.is_liked ? btn.classList.add('active') : btn.classList.remove('active');
                
                let icon = btn.querySelector('i');
                if (data.is_liked) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                } else {
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                }
                
                document.getElementById('proj-like-count-' + projId).innerText = data.count;
            }
        });
    }

    let collabTimeout;
    function searchTeammates(query) {
        clearTimeout(collabTimeout);
        let resultsBox = document.getElementById('collabResults');
        if(query.length < 2) {
            resultsBox.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-muted); font-size:13px; background:var(--bg-light); border-radius:8px; border: 1px dashed var(--border-light);">Type a skill to find project partners.</div>';
            return;
        }
        
        collabTimeout = setTimeout(() => {
            resultsBox.innerHTML = '<div style="text-align:center; padding:20px;"><i class="fa-solid fa-spinner fa-spin" style="color:var(--uiu-orange); font-size: 24px;"></i></div>';
            let fd = new FormData();
            fd.append('ajax_action', 'search_collaborator');
            fd.append('query', query);
            
            fetch('skills.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
                if(data.status === 'success') { resultsBox.innerHTML = data.html; }
            });
        }, 300);
    }

    function showProjectFileName(input) {
        if(input.files && input.files[0]) {
            document.getElementById('uploadText').innerText = "Selected: " + input.files[0].name;
            document.getElementById('uploadBox').style.borderColor = "var(--uiu-orange)";
            document.getElementById('uploadBox').style.background = "var(--uiu-orange-light)";
        }
    }
</script>

<script src="assets/main.js"></script>
</body>
</html>