<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$current_page = 'alumni.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_job'])) {
    $title = $conn->real_escape_string($_POST['job_title']);
    $company = $conn->real_escape_string($_POST['company_name']);
    $location = $conn->real_escape_string($_POST['location']);
    $type = $conn->real_escape_string($_POST['job_type']);
    $link = $conn->real_escape_string($_POST['apply_link']);
    $desc = $conn->real_escape_string($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO jobs (posted_by, title, company, location, job_type, apply_link, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $title, $company, $location, $type, $link, $desc);
    $stmt->execute();
    header("Location: alumni.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['join_alumni'])) {
    $company = $conn->real_escape_string($_POST['current_company']);
    $designation = $conn->real_escape_string($_POST['designation']);
    $linkedin = $conn->real_escape_string($_POST['linkedin_url']);
    $is_mentor = isset($_POST['is_mentor']) ? 1 : 0;

    $check = $conn->query("SELECT id FROM alumni_profiles WHERE user_id = $user_id");
    if($check->num_rows > 0) {
        $conn->query("UPDATE alumni_profiles SET current_company='$company', designation='$designation', linkedin_url='$linkedin', is_mentor=$is_mentor WHERE user_id=$user_id");
    } else {
        $conn->query("INSERT INTO alumni_profiles (user_id, current_company, designation, linkedin_url, is_mentor) VALUES ($user_id, '$company', '$designation', '$linkedin', $is_mentor)");
        $conn->query("UPDATE users SET role='Alumni' WHERE id=$user_id");
        $_SESSION['role'] = 'Alumni';
    }
    header("Location: alumni.php"); exit();
}

$is_alumni = ($user_role === 'Alumni');
$my_alumni_data = null;
if ($is_alumni) {
    $res = $conn->query("SELECT * FROM alumni_profiles WHERE user_id = $user_id");
    if ($res->num_rows > 0) $my_alumni_data = $res->fetch_assoc();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/alumni.css">

<main class="alumni-layout">
    
    <div class="main-column">
        <div class="hero-banner">
            <div>
                <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">Career Opportunities</h1>
                <p style="font-size: 14px; opacity: 0.8;">Exclusive job and internship postings from UIU Alumni network.</p>
            </div>
            <button class="btn-primary" style="background: #fff; color: #0F172A;" onclick="document.getElementById('jobModal').style.display='flex'">
                <i class="fa-solid fa-plus" style="vertical-align: middle; margin-right: 5px; font-size: 16px;"></i> Post a Job
            </button>
        </div>

        <div class="category-filters">
            <button class="cat-btn active" onclick="filterJobs('All', this)">All Roles</button>
            <button class="cat-btn" onclick="filterJobs('Full-Time', this)">Full-Time</button>
            <button class="cat-btn" onclick="filterJobs('Internship', this)">Internship</button>
            <button class="cat-btn" onclick="filterJobs('Part-Time', this)">Part-Time</button>
        </div>

        <div id="jobGrid">
            <?php
            $sql = "SELECT j.*, u.full_name, u.role FROM jobs j JOIN users u ON j.posted_by = u.id ORDER BY j.created_at DESC";
            $res = $conn->query($sql);

            if ($res && $res->num_rows > 0) {
                while ($job = $res->fetch_assoc()) {
                    $badge_class = 'badge-full';
                    if ($job['job_type'] == 'Internship') $badge_class = 'badge-intern';
                    if ($job['job_type'] == 'Part-Time') $badge_class = 'badge-part';
                    ?>
                    <div class="job-card job-item" data-type="<?php echo htmlspecialchars($job['job_type']); ?>">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span class="job-badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <div style="display: flex; gap: 15px; font-size: 13px; color: var(--text-muted); margin-bottom: 12px; font-weight: 500;">
                                    <span style="display: flex; align-items: center; gap: 6px;"><i class="fa-regular fa-building" style="font-size: 14px;"></i> <?php echo htmlspecialchars($job['company']); ?></span>
                                    <span style="display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-location-dot" style="font-size: 14px;"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($job['apply_link']); ?>" target="_blank" class="btn-primary" style="padding: 8px 20px; font-size: 13px; text-decoration: none;">Apply Now</a>
                        </div>
                        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.5; border-top: 1px solid var(--border-light); padding-top: 12px; margin-top: 5px;"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                            <span>Posted by: <a href="profile.php?id=<?php echo $job['posted_by']; ?>" style="color:var(--uiu-orange); text-decoration:none; font-weight:600;"><?php echo htmlspecialchars($job['full_name']); ?></a></span>
                            <span><?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div style="text-align:center; padding: 40px; background: #fff; border-radius: 12px; border: 1px solid var(--border-light); color: var(--text-muted);">No jobs posted yet.</div>';
            }
            ?>
        </div>
    </div>

    <div class="side-column">
        
        <!-- Registration Card -->
        <div class="card" style="margin-bottom: 24px; text-align: center; padding: 24px 20px;">
            <div style="width: 50px; height: 50px; background: #F0F9FF; color: #0284C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
                <i class="fa-solid fa-graduation-cap" style="font-size: 24px;"></i>
            </div>
            <h4 style="font-size: 16px; font-weight: 600; color: var(--text-main); margin-bottom: 8px;">Are you a Graduate?</h4>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Join the directory to help juniors with referrals and mentorship.</p>
            <button class="btn-outline" style="width: 100%; border-color: var(--border-light); color: var(--text-main);" onclick="document.getElementById('alumniModal').style.display='flex'">
                <?php echo $is_alumni ? 'Update Profile' : 'Join Directory'; ?>
            </button>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-users" style="color: var(--uiu-orange); font-size: 18px;"></i> Connect with Alumni
                </h4>
            </div>
            
            <div style="display:flex; flex-direction:column; gap:8px; max-height: 500px; overflow-y: auto;">
                <?php
                $alumni_sql = "SELECT a.*, u.full_name, u.department FROM alumni_profiles a JOIN users u ON a.user_id = u.id ORDER BY a.id DESC";
                $a_res = $conn->query($alumni_sql);

                if ($a_res && $a_res->num_rows > 0) {
                    while($al = $a_res->fetch_assoc()) {
                        echo '<div class="alumni-card">
                                <div class="avatar-sm" style="background:var(--bg-light); border:1px solid var(--border-light); color:var(--text-main); width:40px; height:40px; font-size:14px;">'.strtoupper($al['full_name'][0]).'</div>
                                <div style="flex:1; overflow:hidden;">
                                    <strong style="font-size:14px; color:var(--text-main); display:block; white-space:nowrap; text-overflow:ellipsis;">'.htmlspecialchars($al['full_name']).'</strong>
                                    <span style="font-size:12px; color:var(--text-muted); display:block; white-space:nowrap; text-overflow:ellipsis;">'.htmlspecialchars($al['designation']).' at <span style="color:var(--uiu-orange); font-weight:600;">'.htmlspecialchars($al['current_company']).'</span></span>
                                </div>
                                <a href="messages.php?user='.$al['user_id'].'" style="color:var(--text-muted); transition:0.2s;" onmouseover="this.style.color=\'var(--uiu-orange)\'" onmouseout="this.style.color=\'var(--text-muted)\'" title="Message">
                                    <i class="fa-regular fa-comment-dots" style="font-size: 18px;"></i>
                                </a>
                              </div>';
                    }
                } else {
                    echo '<div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px; background:var(--bg-light); border-radius:8px;">Directory is empty.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</main>

<div class="modal-overlay" id="jobModal">
    <div class="modal-content" style="max-width: 550px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Post a Job / Internship</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('jobModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 20px;"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="post_job" value="1">
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Job Title *</label>
            <input type="text" name="job_title" class="poll-input" placeholder="e.g. Software Engineer Intern" required>

            <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Company *</label>
                    <input type="text" name="company_name" class="poll-input" placeholder="e.g. Google" required>
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Location *</label>
                    <input type="text" name="location" class="poll-input" placeholder="e.g. Dhaka, Remote" required>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Job Type *</label>
                    <select name="job_type" class="poll-input" required>
                        <option value="Full-Time">Full-Time</option>
                        <option value="Part-Time">Part-Time</option>
                        <option value="Internship">Internship</option>
                        <option value="Contract">Contract</option>
                    </select>
                </div>
                <div style="flex: 2;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Apply Link / Email *</label>
                    <input type="url" name="apply_link" class="poll-input" placeholder="https://..." required>
                </div>
            </div>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Description</label>
            <textarea name="description" class="poll-input" rows="3" placeholder="Brief requirements and perks..."></textarea>

            <button type="submit" class="btn-primary" style="width:100%; margin-top: 10px;">Post Opportunity</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="alumniModal">
    <div class="modal-content" style="max-width: 450px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Alumni Registration</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('alumniModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 20px;"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="join_alumni" value="1">
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Current Company *</label>
            <input type="text" name="current_company" class="poll-input" value="<?php echo $my_alumni_data ? htmlspecialchars($my_alumni_data['current_company']) : ''; ?>" required>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Designation / Job Title *</label>
            <input type="text" name="designation" class="poll-input" value="<?php echo $my_alumni_data ? htmlspecialchars($my_alumni_data['designation']) : ''; ?>" required>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">LinkedIn URL</label>
            <input type="url" name="linkedin_url" class="poll-input" value="<?php echo $my_alumni_data ? htmlspecialchars($my_alumni_data['linkedin_url']) : ''; ?>">
            
            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-main); font-weight:600; margin: 15px 0; cursor:pointer;">
                <input type="checkbox" name="is_mentor" value="1" <?php echo ($my_alumni_data && $my_alumni_data['is_mentor']) ? 'checked' : ''; ?>> 
                I am open to mentoring juniors
            </label>

            <button type="submit" class="btn-primary" style="width:100%; margin-top: 10px;">Save Profile</button>
        </form>
    </div>
</div>

<script>
    function filterJobs(type, btnElement) {
        document.querySelectorAll('.cat-btn').forEach(el => el.classList.remove('active'));
        btnElement.classList.add('active');

        let jobs = document.querySelectorAll('.job-item');
        jobs.forEach(job => {
            if (type === 'All' || job.getAttribute('data-type') === type) {
                job.style.display = 'block';
            } else {
                job.style.display = 'none';
            }
        });
    }
</script>

<script src="assets/main.js"></script>
</body>
</html>