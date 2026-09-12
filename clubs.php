<?php
// clubs.php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$current_page = 'clubs.php';

// Handle AJAX Request: Join/Leave Club
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    if ($_POST['ajax_action'] == 'toggle_join') {
        $club_id = intval($_POST['club_id']);
        
        $check = $conn->query("SELECT id FROM club_members WHERE club_id = $club_id AND user_id = $user_id");
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM club_members WHERE club_id = $club_id AND user_id = $user_id");
            $is_member = false;
        } else {
            $conn->query("INSERT INTO club_members (club_id, user_id) VALUES ($club_id, $user_id)");
            $is_member = true;
        }
        $count = $conn->query("SELECT COUNT(id) as c FROM club_members WHERE club_id = $club_id")->fetch_assoc()['c'];
        
        echo json_encode(['status' => 'success', 'is_member' => $is_member, 'count' => $count]);
        exit();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/clubs.css">

<div id="toastMessage" class="toast">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #10B981;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
    <span id="toastText">Action successful</span>
</div>

<main class="clubs-layout">
    
    <div class="main-column">
        <div class="club-header">
            <div>
                <h1 class="club-title">Clubs & Societies</h1>
                <p class="club-subtitle">Connect, collaborate, and grow beyond the classroom.</p>
            </div>
            <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity: 0.2;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>

        <div class="category-filters" id="clubFilters">
            <button class="cat-btn active" onclick="filterClubs('All', this)">All Clubs</button>
            <button class="cat-btn" onclick="filterClubs('Technology', this)">Technology</button>
            <button class="cat-btn" onclick="filterClubs('Cultural', this)">Cultural</button>
            <button class="cat-btn" onclick="filterClubs('Sports', this)">Sports</button>
            <button class="cat-btn" onclick="filterClubs('Business', this)">Business</button>
        </div>

        <div class="club-grid" id="clubGrid">
            <?php
            $sql = "SELECT c.*, 
                           (SELECT COUNT(id) FROM club_members WHERE club_id = c.id) as member_count,
                           (SELECT COUNT(id) FROM club_members WHERE club_id = c.id AND user_id = $user_id) as is_joined
                    FROM clubs c ORDER BY c.name ASC";
            $res = $conn->query($sql);

            if ($res && $res->num_rows > 0) {
                while ($c = $res->fetch_assoc()) {
                    $initials = strtoupper(explode(' ', $c['name'])[0][0] . (isset(explode(' ', $c['name'])[1]) ? explode(' ', $c['name'])[1][0] : ''));
                    $is_joined = $c['is_joined'] > 0;
                    $btn_class = $is_joined ? 'btn-joined' : 'btn-not-joined';
                    $btn_text = $is_joined ? 'Joined' : 'Join Club';
                    ?>
                    <div class="club-card" data-category="<?php echo htmlspecialchars($c['category']); ?>">
                        <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 12px;">
                            <div class="club-logo"><?php echo $initials; ?></div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; line-height: 1.3;"><?php echo htmlspecialchars($c['name']); ?></h3>
                                <span class="club-badge"><?php echo htmlspecialchars($c['category']); ?></span>
                            </div>
                        </div>
                        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 15px; flex: 1;"><?php echo htmlspecialchars($c['description']); ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted); display:flex; align-items:center; gap:4px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span id="member-count-<?php echo $c['id']; ?>"><?php echo $c['member_count']; ?></span> Members
                            </span>
                            <button class="join-btn <?php echo $btn_class; ?>" id="join-btn-<?php echo $c['id']; ?>" onclick="toggleJoin(<?php echo $c['id']; ?>)" style="width: 100px;">
                                <?php echo $btn_text; ?>
                            </button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div style="grid-column: 1/-1; text-align:center; padding: 40px; color: var(--text-muted); background: #fff; border-radius: 12px; border: 1px solid var(--border-light);">No clubs found.</div>';
            }
            ?>
        </div>
    </div>

    <div class="side-column">
        
        <!-- Upcoming Events Widget -->
        <div class="card" style="margin-bottom: 24px;">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--uiu-orange);"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Upcoming Events
            </h4>
            
            <div id="eventsList">
                <?php
                $events_sql = "SELECT e.*, c.name as club_name FROM club_events e JOIN clubs c ON e.club_id = c.id WHERE e.event_date >= CURDATE() ORDER BY e.event_date ASC LIMIT 4";
                $e_res = $conn->query($events_sql);
                
                if ($e_res && $e_res->num_rows > 0) {
                    while($e = $e_res->fetch_assoc()) {
                        echo '<div class="event-card">
                                <strong style="font-size: 13px; color: var(--text-main); display: block; margin-bottom: 4px;">'.htmlspecialchars($e['title']).'</strong>
                                <div style="font-size: 11px; color: var(--uiu-orange); font-weight: 600; margin-bottom: 4px;">By '.htmlspecialchars($e['club_name']).'</div>
                                <div style="font-size: 12px; color: var(--text-muted); display:flex; align-items:center; gap:4px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> '.date('M d, g:i A', strtotime($e['event_date'])).'
                                </div>
                              </div>';
                    }
                } else {
                    echo '<div style="text-align:center; padding:15px; color:var(--text-muted); font-size:12px; background:var(--bg-light); border-radius:8px;">No upcoming events.</div>';
                }
                ?>
            </div>
        </div>

        <!-- My Clubs-->
        <div class="card">
            <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #3B82F6;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> My Memberships
            </h4>
            <div id="myClubsList" style="display:flex; flex-direction:column; gap:10px;">
                <?php
                $my_clubs = "SELECT c.name, m.role FROM clubs c JOIN club_members m ON c.id = m.club_id WHERE m.user_id = $user_id ORDER BY c.name ASC";
                $mc_res = $conn->query($my_clubs);
                
                if ($mc_res && $mc_res->num_rows > 0) {
                    while($mc = $mc_res->fetch_assoc()) {
                        echo '<div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:var(--bg-light); border:1px solid var(--border-light); border-radius:8px;">
                                <span style="font-size:13px; font-weight:600; color:var(--text-main);">'.htmlspecialchars($mc['name']).'</span>
                                <span style="font-size:11px; background:#fff; padding:2px 8px; border-radius:12px; border:1px solid var(--border-light); color:var(--text-muted);">'.htmlspecialchars($mc['role']).'</span>
                              </div>';
                    }
                } else {
                    echo '<div style="text-align:center; padding:15px; color:var(--text-muted); font-size:12px; background:var(--bg-light); border-radius:8px;">You haven\'t joined any clubs yet.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</main>

<script>
    function showToast(message) {
        let toast = document.getElementById("toastMessage");
        document.getElementById("toastText").innerText = message;
        toast.className = "toast show";
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    }

    function filterClubs(category, btnElement) {
        document.querySelectorAll('.cat-btn').forEach(el => el.classList.remove('active'));
        btnElement.classList.add('active');

        let cards = document.querySelectorAll('.club-card');
        cards.forEach(card => {
            if (category === 'All' || card.getAttribute('data-category') === category) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function toggleJoin(clubId) {
        let btn = document.getElementById('join-btn-' + clubId);
        let fd = new FormData();
        fd.append('ajax_action', 'toggle_join');
        fd.append('club_id', clubId);

        fetch('clubs.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.is_member) {
                    btn.className = 'join-btn btn-joined';
                    btn.innerText = 'Joined';
                    showToast("Welcome to the club!");
                } else {
                    btn.className = 'join-btn btn-not-joined';
                    btn.innerText = 'Join Club';
                    showToast("You left the club.");
                }
                document.getElementById('member-count-' + clubId).innerText = data.count;
                
                setTimeout(() => location.reload(), 1000);
            }
        });
    }
</script>

<script src="assets/main.js"></script>
</body>
</html>