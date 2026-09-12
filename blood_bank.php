<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$current_page = 'blood_bank.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax_action'] == 'filter_donors') {
        $bg = $conn->real_escape_string($_POST['blood_group']);
        
        $sql = "SELECT d.*, u.full_name, u.department FROM blood_donors d JOIN users u ON d.user_id = u.id WHERE d.is_available = 1";
        if ($bg !== 'All') { $sql .= " AND d.blood_group = '$bg'"; }
        $sql .= " ORDER BY d.last_donation ASC";
        
        $res = $conn->query($sql);
        $html = '';
        
        if ($res->num_rows > 0) {
            while ($donor = $res->fetch_assoc()) {
                $initial = strtoupper($donor['full_name'][0]);
                $last_don = $donor['last_donation'] ? date('M Y', strtotime($donor['last_donation'])) : 'Never';
                
                $html .= '<div style="display:flex; align-items:center; gap:12px; padding:12px; border:1px solid var(--border-light); border-radius:8px; background:var(--bg-light); margin-bottom:10px;">
                            <div style="width:40px; height:40px; background:#FEE2E2; color:#EF4444; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:14px; flex-shrink:0;">'.$initial.'</div>
                            <div style="flex:1;">
                                <strong style="font-size:14px; color:var(--text-main); display:block;">'.htmlspecialchars($donor['full_name']).'</strong>
                                <span style="font-size:12px; color:var(--text-muted);">Dep: '.htmlspecialchars($donor['department']).' • Last: '.$last_don.'</span>
                            </div>
                            <div style="text-align:right;">
                                <strong style="color:#EF4444; font-size:16px; display:block;">'.htmlspecialchars($donor['blood_group']).'</strong>
                                <a href="tel:'.htmlspecialchars($donor['contact_number']).'" style="font-size:12px; color:#10B981; text-decoration:none; font-weight:600;">Call</a>
                            </div>
                          </div>';
            }
        } else {
            $html = '<div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">No available donors found for this group.</div>';
        }
        echo json_encode(['html' => $html]); exit();
    }
    
    if ($_POST['ajax_action'] == 'fulfill_request') {
        $req_id = intval($_POST['request_id']);
        $conn->query("UPDATE blood_requests SET status = 'fulfilled' WHERE id = $req_id AND requester_id = $user_id");
        echo json_encode(['status' => 'success']); exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_blood'])) {
    $patient = $conn->real_escape_string($_POST['patient_name']);
    $bg = $conn->real_escape_string($_POST['blood_group']);
    $bags = intval($_POST['bags_needed']);
    $hospital = $conn->real_escape_string($_POST['hospital_name']);
    $urgency = $conn->real_escape_string($_POST['urgency_level']);
    $date = $conn->real_escape_string($_POST['needed_date']);
    $contact = $conn->real_escape_string($_POST['contact_number']);
    
    $stmt = $conn->prepare("INSERT INTO blood_requests (requester_id, patient_name, blood_group, bags_needed, hospital_name, urgency_level, needed_date, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississss", $user_id, $patient, $bg, $bags, $hospital, $urgency, $date, $contact);
    $stmt->execute();
    header("Location: blood_bank.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_donor'])) {
    $bg = $conn->real_escape_string($_POST['donor_bg']);
    $last_date = !empty($_POST['last_donation']) ? $conn->real_escape_string($_POST['last_donation']) : NULL;
    $contact = $conn->real_escape_string($_POST['donor_contact']);
    
    $check = $conn->query("SELECT id FROM blood_donors WHERE user_id = $user_id");
    if($check->num_rows > 0) {
        $conn->query("UPDATE blood_donors SET blood_group='$bg', last_donation='$last_date', contact_number='$contact', is_available=1 WHERE user_id=$user_id");
    } else {
        $conn->query("INSERT INTO blood_donors (user_id, blood_group, last_donation, contact_number) VALUES ($user_id, '$bg', '$last_date', '$contact')");
    }
    header("Location: blood_bank.php"); exit();
}

$is_donor = false;
$donor_check = $conn->query("SELECT * FROM blood_donors WHERE user_id = $user_id");
if ($donor_check->num_rows > 0) { $is_donor = true; $my_donor_info = $donor_check->fetch_assoc(); }

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/blood_bank.css">


<!-- MAIN CONTENT -->
<main class="dashboard-layout">
    
    <div class="feed-column">
        <div class="blood-header">
            <div>
                <h1 class="blood-title">UIU Blood Bank</h1>
                <p class="blood-subtitle">Donate blood, save a life within the campus community.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-white" onclick="document.getElementById('requestModal').style.display='flex'">
                    <i class="fa-solid fa-hand-holding-droplet" style="vertical-align: middle; margin-right: 4px; font-size: 16px;"></i> Request Blood
                </button>
            </div>
        </div>

        <h3 style="font-size: 18px; font-weight: 600; color: var(--text-main); margin-bottom: 20px;">Active Emergency Requests</h3>
        
        <?php
        $req_sql = "SELECT r.*, u.full_name FROM blood_requests r JOIN users u ON r.requester_id = u.id WHERE r.status = 'active' ORDER BY r.created_at DESC";
        $req_res = $conn->query($req_sql);

        if ($req_res && $req_res->num_rows > 0) {
            while ($req = $req_res->fetch_assoc()) {
                $urgency_class = strtolower($req['urgency_level']);
                ?>
                <div class="req-card <?php echo $urgency_class; ?>" id="req-<?php echo $req['id']; ?>">
                    <div class="req-bg"><?php echo htmlspecialchars($req['blood_group']); ?></div>
                    <div class="req-content">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div>
                                <span class="badge <?php echo $urgency_class; ?>"><?php echo htmlspecialchars($req['urgency_level']); ?> Need</span>
                                <h3 style="font-size: 18px; color: var(--text-main); margin-top: 8px;"><?php echo htmlspecialchars($req['bags_needed']); ?> Bag(s) of <?php echo htmlspecialchars($req['blood_group']); ?></h3>
                            </div>
                            <?php if($req['requester_id'] == $user_id): ?>
                                <button onclick="markFulfilled(<?php echo $req['id']; ?>)" style="background: #10B981; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">Mark Fulfilled</button>
                            <?php endif; ?>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px; color: var(--text-main); margin-bottom: 15px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="fa-regular fa-user" style="color:var(--text-muted); font-size: 16px;"></i> Patient: <strong><?php echo htmlspecialchars($req['patient_name']); ?></strong>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="fa-regular fa-hospital" style="color:var(--text-muted); font-size: 16px;"></i> Hospital: <strong><?php echo htmlspecialchars($req['hospital_name']); ?></strong>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="fa-regular fa-calendar" style="color:var(--text-muted); font-size: 16px;"></i> Needed By: <strong style="color: #EF4444;"><?php echo date('d M, Y', strtotime($req['needed_date'])); ?></strong>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-phone" style="color:var(--text-muted); font-size: 14px;"></i> Contact: <strong><?php echo htmlspecialchars($req['contact_number']); ?></strong>
                            </div>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); border-top: 1px dashed var(--border-light); padding-top: 12px; display: flex; justify-content: space-between;">
                            <span>Requested by: <?php echo htmlspecialchars($req['full_name']); ?></span>
                            <span>Posted <?php echo date('M d', strtotime($req['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div style="text-align:center; padding: 40px; background: #fff; border-radius: 12px; border: 1px solid var(--border-light); color: var(--text-muted);">No active blood requests. Alhamdulillah!</div>';
        }
        ?>
    </div>

    <div class="sidebar-column">
        
        <!-- Donor Registration Card -->
        <div class="card" style="margin-bottom: 24px; text-align: center; padding: 30px 20px;">
            <div style="width: 60px; height: 60px; background: #FEE2E2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                <i class="fa-solid fa-droplet" style="font-size: 28px;"></i>
            </div>
            <?php if($is_donor): ?>
                <h4 style="font-size: 16px; font-weight: 600; color: var(--text-main); margin-bottom: 8px;">You are a Registered Donor!</h4>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Blood Group: <strong style="color: #EF4444;"><?php echo $my_donor_info['blood_group']; ?></strong></p>
                <button class="btn-outline" style="width: 100%; border-color: var(--border-light); color: var(--text-main);" onclick="document.getElementById('donorModal').style.display='flex'">Update Info</button>
            <?php else: ?>
                <h4 style="font-size: 16px; font-weight: 600; color: var(--text-main); margin-bottom: 8px;">Be a Hero Today</h4>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Register as a blood donor in the campus directory.</p>
                <button class="btn-primary" style="width: 100%; background: #EF4444; border: none;" onclick="document.getElementById('donorModal').style.display='flex'">Register as Donor</button>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h4 style="font-size: 16px; font-weight: 600; color: var(--text-main);">Donor Directory</h4>
                <select class="filter-select" id="donorFilter" onchange="loadDonors()">
                    <option value="All">All Groups</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>
            
            <div id="donorList" style="max-height: 400px; overflow-y: auto;">
                <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">Loading donors...</div>
            </div>
        </div>
    </div>
</main>

<!-- Request Blood-->
<div class="modal-overlay" id="requestModal">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main); display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-droplet" style="color:#EF4444; font-size: 18px;"></i> Emergency Request</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('requestModal').style.display='none'"><i class="fa-solid fa-xmark" style="font-size: 20px;"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="request_blood" value="1">
            <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                <div style="flex: 2;">
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Patient Name *</label>
                    <input type="text" name="patient_name" class="poll-input" required>
                </div>
                <div style="flex: 1;">
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Blood Group *</label>
                    <select name="blood_group" class="poll-input" required>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                <div style="flex: 1;">
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Bags Needed *</label>
                    <input type="number" name="bags_needed" class="poll-input" value="1" min="1" required>
                </div>
                <div style="flex: 1;">
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Urgency *</label>
                    <select name="urgency_level" class="poll-input" required>
                        <option value="Normal">Normal</option>
                        <option value="Urgent" selected>Urgent</option>
                        <option value="Critical">Critical (Immediate)</option>
                    </select>
                </div>
            </div>

            <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Hospital Name & Location *</label>
            <input type="text" name="hospital_name" class="poll-input" placeholder="e.g., Evercare Hospital, Bashundhara" required>

            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Needed By Date *</label>
                    <input type="date" name="needed_date" class="poll-input" required>
                </div>
                <div style="flex: 1;">
                    <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Contact Number *</label>
                    <input type="text" name="contact_number" class="poll-input" placeholder="01XXXXXXXXX" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; background:#EF4444; border:none; padding:12px;">Post Blood Request</button>
        </form>
    </div>
</div>

<!-- Donor Registration -->
<div class="modal-overlay" id="donorModal">
    <div class="modal-content" style="max-width: 400px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">Donor Registration</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="document.getElementById('donorModal').style.display='none'"><i class="fa-solid fa-xmark" style="font-size: 20px;"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="register_donor" value="1">
            <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Your Blood Group *</label>
            <select name="donor_bg" class="poll-input" required>
                <option value="A+" <?php echo ($is_donor && $my_donor_info['blood_group']=='A+') ? 'selected' : ''; ?>>A+</option>
                <option value="A-" <?php echo ($is_donor && $my_donor_info['blood_group']=='A-') ? 'selected' : ''; ?>>A-</option>
                <option value="B+" <?php echo ($is_donor && $my_donor_info['blood_group']=='B+') ? 'selected' : ''; ?>>B+</option>
                <option value="B-" <?php echo ($is_donor && $my_donor_info['blood_group']=='B-') ? 'selected' : ''; ?>>B-</option>
                <option value="O+" <?php echo ($is_donor && $my_donor_info['blood_group']=='O+') ? 'selected' : ''; ?>>O+</option>
                <option value="O-" <?php echo ($is_donor && $my_donor_info['blood_group']=='O-') ? 'selected' : ''; ?>>O-</option>
                <option value="AB+" <?php echo ($is_donor && $my_donor_info['blood_group']=='AB+') ? 'selected' : ''; ?>>AB+</option>
                <option value="AB-" <?php echo ($is_donor && $my_donor_info['blood_group']=='AB-') ? 'selected' : ''; ?>>AB-</option>
            </select>
            
            <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Last Donation Date (Leave blank if never)</label>
            <input type="date" name="last_donation" class="poll-input" value="<?php echo $is_donor ? $my_donor_info['last_donation'] : ''; ?>">
            
            <label style="font-size:13px; color:var(--text-muted); font-weight:600; display:block; margin-bottom:5px;">Contact Number *</label>
            <input type="text" name="donor_contact" class="poll-input" value="<?php echo $is_donor ? $my_donor_info['contact_number'] : ''; ?>" required>
            
            <button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">Save Info</button>
        </form>
    </div>
</div>

</div>
<script>
    function loadDonors() {
        let bg = document.getElementById('donorFilter').value;
        let fd = new FormData();
        fd.append('ajax_action', 'filter_donors');
        fd.append('blood_group', bg);
        
        document.getElementById('donorList').innerHTML = '<div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">Loading donors...</div>';
        
        fetch('blood_bank.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            document.getElementById('donorList').innerHTML = data.html;
        });
    }

    function markFulfilled(reqId) {
        if(confirm("Mark this request as fulfilled? It will be removed from the active list.")) {
            let fd = new FormData();
            fd.append('ajax_action', 'fulfill_request');
            fd.append('request_id', reqId);
            
            fetch('blood_bank.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if(data.status === 'success') {
                    let card = document.getElementById('req-' + reqId);
                    card.style.opacity = '0.5';
                    setTimeout(() => card.remove(), 500);
                }
            });
        }
    }

    window.onload = loadDonors;
</script>

<script src="assets/main.js"></script>
</body>
</html>