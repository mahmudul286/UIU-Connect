<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$current_page = 'counseling.php';

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax_action'] == 'update_status') {
        $appt_id = intval($_POST['appointment_id']);
        $new_status = $conn->real_escape_string($_POST['status']);
        
        $check = $conn->query("SELECT id FROM appointments WHERE id = $appt_id AND faculty_id = $user_id");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE appointments SET status = '$new_status' WHERE id = $appt_id");
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
        exit();
    }

    if ($_POST['ajax_action'] == 'get_slots') {
        $fac_id = intval($_POST['faculty_id']);
        $res = $conn->query("SELECT * FROM faculty_slots WHERE faculty_id = $fac_id ORDER BY FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')");
        $html = '<div style="background: var(--bg-light); border: 1px solid var(--border-light); padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 12px; color: var(--text-main);">';
        $html .= '<strong style="display:block; margin-bottom:5px;">Available Counseling Hours:</strong>';
        if($res->num_rows > 0){
            while($s = $res->fetch_assoc()){
                $html .= "• {$s['day_of_week']}: " . date('h:i A', strtotime($s['start_time'])) . " - " . date('h:i A', strtotime($s['end_time'])) . "<br>";
            }
        } else {
            $html .= "<span style='color:#EF4444;'>No slots added by faculty. Please contact them directly.</span>";
        }
        $html .= '</div>';
        echo json_encode(['html' => $html]); exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $faculty_id = intval($_POST['faculty_id']);
    $date = $conn->real_escape_string($_POST['appointment_date']);
    $time = $conn->real_escape_string($_POST['appointment_time']);
    $reason = $conn->real_escape_string($_POST['reason']);

    $day_name = date('l', strtotime($date));
    $check_slot = $conn->query("SELECT id FROM faculty_slots WHERE faculty_id = $faculty_id AND day_of_week = '$day_name' AND start_time <= '$time' AND end_time >= '$time'");
    
    if ($check_slot->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO appointments (student_id, faculty_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $user_id, $faculty_id, $date, $time, $reason);
        $stmt->execute();
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
<div id="toastMessage" class="toast">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #10B981;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
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
            <button class="btn-primary" style="background: #fff; color: #0284C7;" onclick="document.getElementById('bookModal').style.display='flex'">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 5px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Book Session
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
                                <span style="display:flex; align-items:center; gap:5px; color: #0284C7;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> <?php echo date('l, M d, Y', strtotime($row['appointment_date'])); ?></span>
                                <span style="display:flex; align-items:center; gap:5px; color: #0284C7;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <?php echo date('h:i A', strtotime($row['appointment_time'])); ?></span>
                            </div>
                            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin:0;"><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($row['reason'])); ?></p>
                        </div>
                        
                        <?php if($user_role !== 'Student' && in_array($row['status'], ['Pending', 'Approved'])): ?>
                            <div style="display: flex; gap: 10px; border-top: 1px solid var(--border-light); padding-top: 12px; margin-top: 5px;" id="actions-<?php echo $appt_id; ?>">
                                <?php if($row['status'] === 'Pending'): ?>
                                    <button class="btn-primary" style="background: #10B981; border: none; padding: 6px 16px; font-size: 12px;" onclick="updateStatus(<?php echo $appt_id; ?>, 'Approved')">Approve</button>
                                    <button class="btn-outline" style="color: #EF4444; border-color: #FCA5A5; padding: 6px 16px; font-size: 12px;" onclick="updateStatus(<?php echo $appt_id; ?>, 'Rejected')">Reject</button>
                                <?php elseif($row['status'] === 'Approved'): ?>
                                    <button class="btn-primary" style="background: #0284C7; border: none; padding: 6px 16px; font-size: 12px;" onclick="updateStatus(<?php echo $appt_id; ?>, 'Completed')">Mark as Completed</button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            } else {
                echo '<div style="text-align:center; padding: 60px; background: #fff; border-radius: 12px; border: 1px solid var(--border-light); color: var(--text-muted);">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:15px; opacity:0.3;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><br>
                        No appointments found.
                      </div>';
            }
            ?>
        </div>
    </div>

    <div class="side-column">
        
        <?php if($user_role === 'Faculty'): ?>
            <!-- FACULTY: Manage Slots -->
            <div class="card" style="margin-bottom: 24px;">
                <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 15px;">My Counseling Slots</h4>
                
                <div style="margin-bottom: 15px; max-height: 200px; overflow-y: auto;">
                    <?php
                    $slots = $conn->query("SELECT * FROM faculty_slots WHERE faculty_id = $user_id ORDER BY FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')");
                    if($slots->num_rows > 0) {
                        while($s = $slots->fetch_assoc()) {
                            echo '<div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:var(--bg-light); border:1px solid var(--border-light); border-radius:6px; margin-bottom:8px; font-size:12px;">
                                    <div><strong>'.$s['day_of_week'].'</strong><br><span style="color:var(--text-muted);">'.date('h:i A', strtotime($s['start_time'])).' - '.date('h:i A', strtotime($s['end_time'])).'</span></div>
                                    <a href="counseling.php?delete_slot='.$s['id'].'" style="color:#EF4444; font-weight:bold; text-decoration:none;">✕</a>
                                  </div>';
                        }
                    } else {
                        echo '<p style="font-size:12px; color:var(--text-muted);">No slots added yet.</p>';
                    }
                    ?>
                </div>

                <form method="POST">
                    <input type="hidden" name="add_slot" value="1">
                    <select name="day_of_week" class="poll-input" style="padding: 6px; font-size: 12px;" required>
                        <option value="Sunday">Sunday</option><option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option><option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input type="time" name="start_time" class="poll-input" style="padding: 6px; font-size: 12px;" required>
                        <input type="time" name="end_time" class="poll-input" style="padding: 6px; font-size: 12px;" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; font-size: 12px; padding: 8px;">Add Slot</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Faculty Directory -->
        <div class="card">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #0284C7;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg> Faculty Directory
            </h4>
            <div style="position: relative; margin-bottom: 16px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" id="facultySearch" class="poll-input" style="margin-bottom: 0; padding-left: 36px;" placeholder="Search faculty name..." onkeyup="searchFaculty(this.value)">
            </div>
            
            <div id="facultyList" style="max-height: 450px; overflow-y: auto; padding-right: 5px;">
                <?php
                $fac_sql = "SELECT id, full_name, department FROM users WHERE role = 'Faculty' ORDER BY full_name ASC";
                $fac_res = $conn->query($fac_sql);
                if ($fac_res && $fac_res->num_rows > 0) {
                    while ($f = $fac_res->fetch_assoc()) {
                        echo '<div class="faculty-card faculty-item" onclick="selectFaculty('.$f['id'].', \''.htmlspecialchars($f['full_name']).'\')">
                                <div class="avatar-sm" style="background:var(--bg-light); border:1px solid var(--border-light); color:var(--text-main); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">'.strtoupper($f['full_name'][0]).'</div>
                                <div>
                                    <strong class="fac-name" style="font-size:14px; color:var(--text-main); display:block;">'.htmlspecialchars($f['full_name']).'</strong>
                                    <span style="font-size:12px; color:var(--text-muted);">'.htmlspecialchars($f['department']).'</span>
                                </div>
                              </div>';
                    }
                } else {
                    echo '<div style="text-align:center; padding:15px; color:var(--text-muted); font-size:13px;">No faculty members registered yet.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</main>

<!-- Book Appointment -->
<div class="modal-overlay" id="bookModal">
    <div class="modal-content" style="max-width: 500px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Book Counseling Session</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('bookModal').style.display='none'">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'invalid_time'): ?>
            <div style="background: #FEE2E2; color: #DC2626; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 15px; text-align: center;">
                Selected time is outside the faculty's available slots. Please check their schedule below.
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="book_appointment" value="1">
            <input type="hidden" name="faculty_id" id="selectedFacultyId" required>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Selected Faculty *</label>
            <input type="text" id="selectedFacultyName" class="poll-input" placeholder="Click on a faculty from the right directory" readonly style="background: var(--bg-light); font-weight: 600; cursor: not-allowed;" required>

            <div id="facultySlotsHint"></div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Date *</label>
                    <input type="date" name="appointment_date" class="poll-input" style="margin-bottom: 0;" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Preferred Time *</label>
                    <input type="time" name="appointment_time" class="poll-input" style="margin-bottom: 0;" required>
                </div>
            </div>
            
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Reason for Appointment *</label>
            <textarea name="reason" class="poll-input" rows="3" placeholder="E.g. Discussion regarding FYP, Course Advising, etc." required></textarea>

            <button type="submit" class="btn-primary" style="width:100%; margin-top: 10px; padding: 12px; font-size: 14px; background: #0284C7; border: none;">Submit Request</button>
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

    function searchFaculty(query) {
        query = query.toLowerCase();
        let items = document.querySelectorAll('.faculty-item');
        items.forEach(item => {
            let name = item.querySelector('.fac-name').innerText.toLowerCase();
            if (name.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function selectFaculty(id, name) {
        <?php if($user_role !== 'Student'): ?>
            showToast("Only students can book appointments.");
            return;
        <?php endif; ?>
        document.getElementById('selectedFacultyId').value = id;
        document.getElementById('selectedFacultyName').value = name;
        
        let fd = new FormData();
        fd.append('ajax_action', 'get_slots');
        fd.append('faculty_id', id);
        
        fetch('counseling.php', { method: 'POST', body: fd })
        .then(r=>r.json())
        .then(data => {
            document.getElementById('facultySlotsHint').innerHTML = data.html;
        });

        document.getElementById('bookModal').style.display = 'flex';
    }

    function updateStatus(apptId, status) {
        let fd = new FormData();
        fd.append('ajax_action', 'update_status');
        fd.append('appointment_id', apptId);
        fd.append('status', status);
        
        fetch('counseling.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            if(data.status === 'success') {
                let badge = document.getElementById('badge-' + apptId);
                let card = document.getElementById('appt-' + apptId);
                
                badge.innerText = status;
                badge.className = 'appt-badge badge-' + status.toLowerCase();
                card.setAttribute('data-status', status);
                
                let actionsDiv = document.getElementById('actions-' + apptId);
                if(status === 'Approved') {
                    actionsDiv.innerHTML = '<button class="btn-primary" style="background: #0284C7; border: none; padding: 6px 16px; font-size: 12px;" onclick="updateStatus('+apptId+', \'Completed\')">Mark as Completed</button>';
                } else {
                    if(actionsDiv) actionsDiv.remove();
                }
                showToast("Appointment marked as " + status);
            }
        });
    }

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'invalid_time'): ?>
        document.getElementById('bookModal').style.display = 'flex';
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'booked'): ?>
        window.onload = () => showToast('Appointment booked successfully!');
    <?php endif; ?>
</script>

<script src="assets/main.js"></script>
</body>
</html>