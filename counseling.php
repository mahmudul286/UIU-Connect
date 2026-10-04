<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

date_default_timezone_set('Asia/Dhaka'); 

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$current_page = 'counseling.php';

$conn->query("ALTER TABLE notifications MODIFY COLUMN type ENUM('like','comment','connection','announcement','appointment') NOT NULL");

// Faculty: Add Slot
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_slot']) && $user_role === 'Faculty') {
    $day = $conn->real_escape_string($_POST['day_of_week']);
    $start = $conn->real_escape_string($_POST['start_time']);
    $end = $conn->real_escape_string($_POST['end_time']);
    $conn->query("INSERT INTO faculty_slots (faculty_id, day_of_week, start_time, end_time) VALUES ($user_id, '$day', '$start', '$end')");
    header("Location: counseling.php"); exit();
}

// Faculty: Delete Slot
if (isset($_GET['delete_slot']) && $user_role === 'Faculty') {
    $slot_id = intval($_GET['delete_slot']);
    $conn->query("DELETE FROM faculty_slots WHERE id = $slot_id AND faculty_id = $user_id");
    header("Location: counseling.php"); exit();
}

// Handle AJAX Actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax_action'] == 'update_status') {
        $appt_id = intval($_POST['appointment_id']);
        $new_status = $conn->real_escape_string($_POST['status']);
        
        $check = $conn->query("SELECT id, student_id FROM appointments WHERE id = $appt_id AND faculty_id = $user_id");
        if ($check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $student_id = $row['student_id'];

            $conn->query("UPDATE appointments SET status = '$new_status' WHERE id = $appt_id");
            $conn->query("INSERT INTO notifications (user_id, sender_id, type, reference_id) VALUES ($student_id, $user_id, 'appointment', $appt_id)");

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
        exit();
    }

    if ($_POST['ajax_action'] == 'get_slots') {
        $fac_id = intval($_POST['faculty_id']);
        $res = $conn->query("SELECT * FROM faculty_slots WHERE faculty_id = $fac_id");
        
        if($res->num_rows > 0) {
            $slots = [];
            while($s = $res->fetch_assoc()) { $slots[] = $s; }
            
            $options = '';
            $current_date = date('Y-m-d');
            $current_time = date('H:i:s');

            for($i = 0; $i <= 14; $i++) {
                $date = date('Y-m-d', strtotime("+$i days"));
                $day_name = date('l', strtotime($date));
                
                foreach($slots as $s) {
                    if($s['day_of_week'] == $day_name) {
                        if ($date === $current_date && $s['start_time'] <= $current_time) {
                            continue;
                        }

                        $time_formatted = date('h:i A', strtotime($s['start_time'])) . ' - ' . date('h:i A', strtotime($s['end_time']));
                        $val = $date . '|' . $s['start_time'];
                        $display_date = ($i == 0) ? "Today, " . date('M d', strtotime($date)) : date('l, M d', strtotime($date));
                        
                        $options .= '
                        <label class="slot-radio-label">
                            <input type="radio" name="slot_data" value="'.$val.'" required>
                            <div class="slot-box">
                                <span class="slot-date">'.$display_date.'</span>
                                <span class="slot-time">'.$time_formatted.'</span>
                            </div>
                        </label>';
                    }
                }
            }

            if($options == '') {
                $html = '<div style="color:#EF4444; font-size: 13px; font-weight: 600; margin-bottom: 15px; padding: 10px; background: #FEE2E2; border-radius: 6px; border: 1px solid #FCA5A5;">No upcoming slots available in the next 14 days.</div>';
            } else {
                $html = '<label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Select a Slot *</label>';
                $html .= '<div class="slots-grid">' . $options . '</div>';
            }
        } else {
            $html = '<div style="color:#EF4444; font-size: 13px; font-weight: 600; margin-bottom: 15px; padding: 10px; background: #FEE2E2; border-radius: 6px; border: 1px solid #FCA5A5;">This faculty has not added any counseling slots yet.</div>';
        }
        echo json_encode(['html' => $html]); exit();
    }
}

// Student: Book Appointment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $faculty_id = intval($_POST['faculty_id']);
    $reason = $conn->real_escape_string($_POST['reason']);

    if(isset($_POST['slot_data']) && !empty($_POST['slot_data'])) {
        $slot_data = explode('|', $_POST['slot_data']);
        $date = $conn->real_escape_string($slot_data[0]);
        $time = $conn->real_escape_string($slot_data[1]);

        $stmt = $conn->prepare("INSERT INTO appointments (student_id, faculty_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $user_id, $faculty_id, $date, $time, $reason);
        $stmt->execute();
        $appt_id = $conn->insert_id;

        $conn->query("INSERT INTO notifications (user_id, sender_id, type, reference_id) VALUES ($faculty_id, $user_id, 'appointment', $appt_id)");

        header("Location: counseling.php?msg=booked"); exit();
    } else {
        header("Location: counseling.php?msg=invalid_time"); exit();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/counseling.css">
<style>
    .slots-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; max-height: 220px; overflow-y: auto; padding-right: 5px; }
    .slot-radio-label { cursor: pointer; display: block; }
    .slot-radio-label input[type="radio"] { display: none; }
    .slot-box { border: 1px solid var(--border-light); padding: 12px 10px; border-radius: 8px; text-align: center; background: var(--bg-light); transition: 0.2s ease; }
    .slot-box:hover { border-color: var(--uiu-orange); box-shadow: 0 4px 10px rgba(242, 101, 34, 0.05); }
    .slot-radio-label input[type="radio"]:checked + .slot-box { background: var(--uiu-orange-light); border-color: var(--uiu-orange); box-shadow: 0 0 0 2px rgba(242, 101, 34, 0.2); }
    .slot-date { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; }
    .slot-time { font-size: 11px; color: var(--text-muted); font-weight: 600; }
    .slot-radio-label input[type="radio"]:checked + .slot-box .slot-date { color: var(--uiu-orange); }
</style>

<div id="toastMessage" class="toast">
    <i class="fa-solid fa-check" style="color: #10B981; font-size: 20px;"></i>
    <span id="toastText">Action successful</span>
</div>

<main class="counseling-layout">
    
    <div class="main-column">
        <div class="hero-banner">
            <div>
                <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">Counseling & Advising</h1>
                <p style="font-size: 14px; opacity: 0.9;">Schedule 1-on-1 mentorship or advising sessions with faculty.</p>
            </div>
            <?php if($user_role === 'Student'): ?>
            <button class="btn-primary" style="background: #fff; color: #0284C7;" onclick="openBookModal()">
                <i class="fa-regular fa-calendar-plus" style="vertical-align: middle; margin-right: 5px; font-size: 16px;"></i> Book Session
            </button>
            <?php endif; ?>
        </div>

        <div class="status-tabs">
            <button class="status-tab active" onclick="filterAppointments('All', this)">All Sessions</button>
            <button class="status-tab" onclick="filterAppointments('Pending', this)">Pending</button>
            <button class="status-tab" onclick="filterAppointments('Approved', this)">Upcoming</button>
            <button class="status-tab" onclick="filterAppointments('Completed', this)">History</button>
        </div>

        <div id="appointmentsGrid">
            <?php
            if ($user_role === 'Student') {
                $sql = "SELECT a.*, u.full_name as other_name, u.department FROM appointments a JOIN users u ON a.faculty_id = u.id WHERE a.student_id = $user_id ORDER BY a.created_at DESC";
            } else {
                $sql = "SELECT a.*, u.full_name as other_name, u.department FROM appointments a JOIN users u ON a.student_id = u.id WHERE a.faculty_id = $user_id ORDER BY a.created_at DESC";
            }
            $res = $conn->query($sql);

            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $status_class = 'badge-' . strtolower($row['status']);
                    $appt_id = $row['id'];
                    ?>
                    <div class="appt-card appt-item" data-status="<?php echo htmlspecialchars($row['status']); ?>" id="appt-<?php echo $appt_id; ?>">
                        <div class="appt-header">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <div class="avatar-sm" style="background: var(--bg-light); border: 1px solid var(--border-light); color: var(--text-main); font-size: 16px; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?php echo strtoupper($row['other_name'][0]); ?></div>
                                <div>
                                    <strong style="font-size: 15px; color: var(--text-main); display: block;"><?php echo htmlspecialchars($row['other_name']); ?></strong>
                                    <span style="font-size: 12px; color: var(--text-muted);"><?php echo ($user_role === 'Student' ? 'Faculty' : 'Student') . " • " . htmlspecialchars($row['department']); ?></span>
                                </div>
                            </div>
                            <span class="appt-badge <?php echo $status_class; ?>" id="badge-<?php echo $appt_id; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                        </div>
                        
                        <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px dashed var(--border-light);">
                            <div style="display: flex; gap: 20px; font-size: 13px; color: var(--text-main); font-weight: 600; margin-bottom: 8px;">
                                <span style="display:flex; align-items:center; gap:5px; color: #0284C7;"><i class="fa-regular fa-calendar" style="font-size: 14px;"></i> <?php echo date('l, M d, Y', strtotime($row['appointment_date'])); ?></span>
                                <span style="display:flex; align-items:center; gap:5px; color: #0284C7;"><i class="fa-regular fa-clock" style="font-size: 14px;"></i> <?php echo date('h:i A', strtotime($row['appointment_time'])); ?></span>
                            </div>
                            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin:0;"><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($row['reason'])); ?></p>
                        </div>
                        
                        <?php if($user_role !== 'Student' && in_array($row['status'], ['Pending', 'Approved'])): ?>
                            <div style="display: flex; gap: 10px; border-top: 1px solid var(--border-light); padding-top: 12px; margin-top: 5px;" id="actions-<?php echo $appt_id; ?>">
                                <?php if($row['status'] === 'Pending'): ?>
                                    <button class="btn-primary" style="background: #10B981; border: none; padding: 6px 16px; font-size: 12px;" onclick="updateStatus(<?php echo $appt_id; ?>, 'Approved')"><i class="fa-solid fa-check"></i> Approve</button>
                                    <button class="btn-outline" style="color: #EF4444; border-color: #FCA5A5; padding: 6px 16px; font-size: 12px;" onclick="updateStatus(<?php echo $appt_id; ?>, 'Rejected')">Reject</button>
                                <?php elseif($row['status'] === 'Approved'): ?>
                                    <button class="btn-primary" style="background: #0284C7; border: none; padding: 6px 16px; font-size: 12px;" onclick="updateStatus(<?php echo $appt_id; ?>, 'Completed')"><i class="fa-solid fa-check-double"></i> Mark as Completed</button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            } else {
                echo '<div style="text-align:center; padding: 60px; background: #fff; border-radius: 12px; border: 1px solid var(--border-light); color: var(--text-muted);">
                        <i class="fa-regular fa-calendar-xmark" style="font-size: 48px; margin-bottom:15px; opacity:0.3;"></i><br>
                        No appointments found.
                      </div>';
            }
            ?>
        </div>
    </div>

    <div class="side-column">
        
        <?php if($user_role === 'Faculty'): ?>
            <div class="card" style="margin-bottom: 24px;">
                <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 15px;">My Counseling Slots</h4>
                
                <div style="margin-bottom: 15px; max-height: 200px; overflow-y: auto;">
                    <?php
                    $slots = $conn->query("SELECT * FROM faculty_slots WHERE faculty_id = $user_id ORDER BY FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')");
                    if($slots->num_rows > 0) {
                        while($s = $slots->fetch_assoc()) {
                            echo '<div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:var(--bg-light); border:1px solid var(--border-light); border-radius:6px; margin-bottom:8px; font-size:12px;">
                                    <div><strong>'.$s['day_of_week'].'</strong><br><span style="color:var(--text-muted);">'.date('h:i A', strtotime($s['start_time'])).' - '.date('h:i A', strtotime($s['end_time'])).'</span></div>
                                    <a href="counseling.php?delete_slot='.$s['id'].'" style="color:#EF4444; font-weight:bold; text-decoration:none;" title="Delete Slot">✕</a>
                                  </div>';
                        }
                    } else {
                        echo '<p style="font-size:12px; color:var(--text-muted);">No slots added yet.</p>';
                    }
                    ?>
                </div>

                <form method="POST">
                    <input type="hidden" name="add_slot" value="1">
                    <select name="day_of_week" class="poll-input" style="padding: 6px; font-size: 12px; cursor: pointer;" required>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                    </select>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input type="time" name="start_time" class="poll-input" style="padding: 6px; font-size: 12px;" required>
                        <input type="time" name="end_time" class="poll-input" style="padding: 6px; font-size: 12px;" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; font-size: 12px; padding: 8px;"><i class="fa-solid fa-plus"></i> Add Slot</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if($user_role === 'Student'): ?>
        <div class="card">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-chalkboard-user" style="color: #0284C7; font-size: 18px;"></i> Faculty Directory
            </h4>
            <div style="position: relative; margin-bottom: 16px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
                <input type="text" id="facultySearch" class="poll-input" style="margin-bottom: 0; padding-left: 36px;" placeholder="Search faculty name..." onkeyup="searchFacultySidebar(this.value)">
            </div>
            
            <div id="facultyList" style="max-height: 450px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;">
                <?php
                $fac_sql = "SELECT id, full_name, department FROM users WHERE role = 'Faculty' ORDER BY full_name ASC";
                $fac_res = $conn->query($fac_sql);
                if ($fac_res && $fac_res->num_rows > 0) {
                    while ($f = $fac_res->fetch_assoc()) {
                        // FIX: 100% Responsive, Beautiful UI. Prevents horizontal scroll, truncates large names.
                        echo '<div class="faculty-card faculty-item" style="border: 1px solid var(--border-light); padding: 16px; border-radius: 12px; margin-bottom: 12px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s;">
                                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 15px;">
                                    <div style="width: 42px; height: 42px; background: #F0F9FF; color: #0284C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; flex-shrink:0;">'.strtoupper($f['full_name'][0]).'</div>
                                    <div style="overflow:hidden; flex:1; min-width:0;">
                                        <strong class="fac-name" style="font-size:15px; color:var(--text-main); display:block; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">'.htmlspecialchars($f['full_name']).'</strong>
                                        <span style="font-size:12px; color:var(--text-muted);"><i class="fa-solid fa-briefcase" style="margin-right:4px;"></i>'.htmlspecialchars($f['department']).'</span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button class="btn-primary" style="flex:1; padding: 8px; font-size: 12px; background: #0284C7; border: none; border-radius: 6px; display:flex; align-items:center; justify-content:center; gap:5px;" onclick="selectFaculty('.$f['id'].')" title="Book Counseling"><i class="fa-solid fa-calendar-check"></i> Book</button>
                                    <a href="profile.php?id='.$f['id'].'" class="btn-outline" style="flex:1; padding: 8px; font-size: 12px; border-radius: 6px; display:flex; align-items:center; justify-content:center; gap:5px; text-decoration:none; color:var(--text-main); border-color:var(--border-light);" title="View Faculty Profile"><i class="fa-solid fa-user"></i> Profile</a>
                                </div>
                              </div>';
                    }
                } else {
                    echo '<div style="text-align:center; padding:15px; color:var(--text-muted); font-size:13px;">No faculty members registered yet.</div>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php if($user_role === 'Student'): ?>
<div class="modal-overlay" id="bookModal">
    <div class="modal-content" style="max-width: 480px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Book Counseling Session</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('bookModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 20px;"></i>
            </button>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'invalid_time'): ?>
            <div style="background: #FEE2E2; color: #DC2626; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 15px; text-align: center;">
                Invalid selection. Please try again.
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="book_appointment" value="1">
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Select Faculty *</label>
            <select name="faculty_id" id="modalFacultySelect" class="poll-input" style="cursor: pointer; font-weight: 600;" onchange="fetchSlots(this.value)" required>
                <option value="">-- Choose a Faculty Member --</option>
                <?php
                if (isset($fac_res) && $fac_res && $fac_res->num_rows > 0) {
                    $fac_res->data_seek(0);
                    while($f = $fac_res->fetch_assoc()) {
                        echo '<option value="'.$f['id'].'">'.htmlspecialchars($f['full_name']).' ('.htmlspecialchars($f['department']).')</option>';
                    }
                }
                ?>
            </select>

            <div id="dynamicSlotsContainer">
                <div style="text-align:center; padding:15px; color:var(--text-muted); font-size:13px; border: 1px dashed var(--border-light); border-radius: 8px; margin-bottom: 15px;">Please select a faculty to view available slots.</div>
            </div>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Reason for Appointment *</label>
            <textarea name="reason" class="poll-input" rows="3" placeholder="E.g. Discussion regarding FYDP, Course Advising, etc." required></textarea>

            <button type="submit" id="submitRequestBtn" class="btn-primary" style="width:100%; margin-top: 10px; padding: 12px; font-size: 14px; background: #0284C7; border: none;" disabled>Confirm Booking</button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    function showToast(message, isError = false) {
        let toast = document.getElementById("toastMessage");
        let icon = toast.querySelector("i");
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

    function filterAppointments(status, btnElement) {
        document.querySelectorAll('.status-tab').forEach(el => el.classList.remove('active'));
        btnElement.classList.add('active');

        let appts = document.querySelectorAll('.appt-item');
        appts.forEach(appt => {
            if (status === 'All' || appt.getAttribute('data-status') === status) {
                appt.style.display = 'flex';
            } else {
                appt.style.display = 'none';
            }
        });
    }

    function searchFacultySidebar(query) {
        query = query.toLowerCase();
        let items = document.querySelectorAll('.faculty-item');
        items.forEach(item => {
            let name = item.querySelector('.fac-name').innerText.toLowerCase();
            if (name.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function openBookModal() {
        document.getElementById('bookModal').style.display = 'flex';
        document.getElementById('modalFacultySelect').value = "";
        document.getElementById('dynamicSlotsContainer').innerHTML = '<div style="text-align:center; padding:15px; color:var(--text-muted); font-size:13px; border: 1px dashed var(--border-light); border-radius: 8px; margin-bottom: 15px;">Please select a faculty to view available slots.</div>';
        
        let btn = document.getElementById('submitRequestBtn');
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    }

    function selectFaculty(id) {
        document.getElementById('bookModal').style.display = 'flex';
        document.getElementById('modalFacultySelect').value = id;
        fetchSlots(id);
    }

    function fetchSlots(id) {
        let slotsContainer = document.getElementById('dynamicSlotsContainer');
        let btn = document.getElementById('submitRequestBtn');
        
        if(!id) {
            slotsContainer.innerHTML = '<div style="text-align:center; padding:15px; color:var(--text-muted); font-size:13px; border: 1px dashed var(--border-light); border-radius: 8px; margin-bottom: 15px;">Please select a faculty to view available slots.</div>';
            btn.disabled = true; btn.style.opacity = '0.5'; btn.style.cursor = 'not-allowed';
            return;
        }

        slotsContainer.innerHTML = '<div style="text-align:center; padding:15px;"><i class="fa-solid fa-spinner fa-spin" style="color:var(--uiu-orange);"></i> Finding slots...</div>';
        btn.disabled = true; btn.style.opacity = '0.5'; btn.style.cursor = 'not-allowed';

        let fd = new FormData();
        fd.append('ajax_action', 'get_slots');
        fd.append('faculty_id', id);
        
        fetch('counseling.php', { method: 'POST', body: fd })
        .then(r=>r.json())
        .then(data => {
            slotsContainer.innerHTML = data.html;
            if(data.html.includes('slot-radio-label')) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            }
        });
    }

    function updateStatus(apptId, status) {
        if(!confirm("Are you sure you want to mark this appointment as " + status + "?")) return;

        let fd = new FormData();
        fd.append('ajax_action', 'update_status');
        fd.append('appointment_id', apptId);
        fd.append('status', status);
        
        fetch('counseling.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            if(data.status === 'success') {
                showToast("Appointment marked as " + status);
                setTimeout(() => location.reload(), 1000); 
            } else {
                showToast("Failed to update status", true);
            }
        });
    }

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'invalid_time'): ?>
        window.onload = () => showToast('Failed to book appointment. Time slot was invalid.', true);
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'booked'): ?>
        window.onload = () => showToast('Appointment booked successfully!');
    <?php endif; ?>
</script>

<script src="assets/main.js"></script>
</body>
</html>