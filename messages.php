<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$active_chat_user = isset($_GET['user']) ? intval($_GET['user']) : null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];
    
    if ($action == 'accept_request') {
        $conn_id = intval($_POST['conn_id']);
        $check = $conn->query("SELECT id FROM connections WHERE id = $conn_id AND receiver_id = $user_id");
        if($check->num_rows > 0) {
            $conn->query("UPDATE connections SET status = 'accepted' WHERE id = $conn_id");
            echo json_encode(['status' => 'success']);
        }
        exit();
    }

    if ($action == 'decline_request') {
        $conn_id = intval($_POST['conn_id']);
        $conn->query("DELETE FROM connections WHERE id = $conn_id AND receiver_id = $user_id");
        echo json_encode(['status' => 'success']);
        exit();
    }

    if ($action == 'send_message') {
        $receiver_id = intval($_POST['receiver_id']);
        $msg_text = $conn->real_escape_string($_POST['message_text']);
        if (!empty($msg_text)) {
            $conn->query("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES ($user_id, $receiver_id, '$msg_text')");
        }
        echo json_encode(['status' => 'success']); exit();
    }
    
    if ($action == 'load_messages') {
        $target_id = intval($_POST['target_id']);
        $conn->query("UPDATE messages SET is_read = 1 WHERE sender_id = $target_id AND receiver_id = $user_id");
        
        $sql = "SELECT * FROM messages WHERE (sender_id = $user_id AND receiver_id = $target_id) OR (sender_id = $target_id AND receiver_id = $user_id) ORDER BY created_at ASC";
        $res = $conn->query($sql);
        $html = '';
        if ($res->num_rows > 0) {
            while ($msg = $res->fetch_assoc()) {
                $is_mine = ($msg['sender_id'] == $user_id);
                $bubble_class = $is_mine ? 'msg-mine' : 'msg-theirs';
                $align = $is_mine ? 'flex-end' : 'flex-start';
                $html .= '<div style="display: flex; flex-direction: column; align-items: '.$align.'; margin-bottom: 12px;">
                            <div class="'.$bubble_class.'">'.htmlspecialchars($msg['message_text']).'</div>
                            <small style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">'.date('h:i A', strtotime($msg['created_at'])).'</small>
                          </div>';
            }
        } else {
            $html = '<div style="text-align:center; color:var(--text-muted); padding:40px; font-size:13px;">Say hi to start the conversation!</div>';
        }
        echo json_encode(['html' => $html]); exit();
    }
}

$active_user_name = "Select a chat";
$active_user_status = "";
$active_initials = "";

if ($active_chat_user) {
    $u_res = $conn->query("SELECT full_name, role FROM users WHERE id = $active_chat_user");
    if ($u_res->num_rows > 0) {
        $u_data = $u_res->fetch_assoc();
        $active_user_name = $u_data['full_name'];
        $active_user_status = $u_data['role'];
        $active_initials = strtoupper($u_data['full_name'][0]);
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/messages.css">
 

<div class="messenger-container">
    
    <div class="contacts-panel">
        <div class="contacts-header">
            <h2 style="font-size: 20px; color: var(--text-main);">Messages</h2>
            <div style="position: relative; margin-top: 12px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
                <input type="text" placeholder="Search chats..." style="width: 100%; padding: 8px 12px 8px 36px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 13px; outline: none; background: var(--bg-light); box-sizing: border-box;">
            </div>
        </div>
        
        <div style="overflow-y: auto; flex: 1;">
            
            <?php
            $req_sql = "SELECT c.id as conn_id, u.id as sender_id, u.full_name, u.role FROM connections c JOIN users u ON c.sender_id = u.id WHERE c.receiver_id = $user_id AND c.status = 'pending'";
            $req_res = $conn->query($req_sql);
            if ($req_res && $req_res->num_rows > 0) {
                echo '<div style="padding: 10px 20px; font-size: 11px; font-weight: 700; color: #3B82F6; text-transform: uppercase;">Connection Requests</div>';
                while ($req = $req_res->fetch_assoc()) {
                    ?>
                    <div class="req-card" id="req-card-<?php echo $req['conn_id']; ?>">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar-sm" style="background:var(--bg-light); border:1px solid var(--border-light); color:var(--text-main); width:32px; height:32px; font-size:12px;"><?php echo strtoupper($req['full_name'][0]); ?></div>
                            <div style="flex:1;">
                                <strong style="font-size:14px; color:var(--text-main); display:block;"><?php echo htmlspecialchars($req['full_name']); ?></strong>
                                <small style="color:var(--text-muted); font-size:12px;"><?php echo htmlspecialchars($req['role']); ?></small>
                            </div>
                        </div>
                        <div class="req-actions">
                            <button class="req-btn btn-accept" onclick="handleRequest(<?php echo $req['conn_id']; ?>, 'accept')">Accept</button>
                            <button class="req-btn btn-decline" onclick="handleRequest(<?php echo $req['conn_id']; ?>, 'decline')">Decline</button>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>

            <div style="padding: 10px 20px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Recent Chats</div>
            <?php
            $contact_sql = "SELECT u.id, u.full_name, MAX(m.created_at) as last_msg_time,
                                   SUM(CASE WHEN m.receiver_id = $user_id AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
                            FROM users u 
                            JOIN messages m ON (u.id = m.sender_id AND m.receiver_id = $user_id) OR (u.id = m.receiver_id AND m.sender_id = $user_id) 
                            GROUP BY u.id ORDER BY last_msg_time DESC";
            $contact_res = $conn->query($contact_sql);
            
            if ($contact_res && $contact_res->num_rows > 0) {
                while($c = $contact_res->fetch_assoc()) {
                    $isActive = ($active_chat_user == $c['id']) ? 'active' : '';
                    echo '<a href="messages.php?user='.$c['id'].'" class="contact-item '.$isActive.'">
                            <div class="avatar-sm" style="background:var(--bg-light); border:1px solid var(--border-light); color:var(--text-main);">'.strtoupper($c['full_name'][0]).'</div>
                            <div style="flex:1; overflow:hidden;">
                                <strong style="font-size:14px; color:var(--text-main); display:block; white-space:nowrap; text-overflow:ellipsis;">'.htmlspecialchars($c['full_name']).'</strong>
                                <small style="color:var(--text-muted); font-size:12px;">'.($c['unread_count'] > 0 ? '<span style="color:#EF4444; font-weight:700;">New Message</span>' : 'Click to view chat').'</small>
                            </div>
                            '.($c['unread_count'] > 0 ? '<div style="width:10px; height:10px; background:#EF4444; border-radius:50%;"></div>' : '').'
                          </a>';
                }
            } else {
                echo '<div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">No recent conversations. Go to a profile to send a message!</div>';
            }
            ?>
        </div>
    </div>

    <div class="chat-panel">
        <?php if ($active_chat_user): ?>
            <div class="chat-header">
                <div class="avatar-sm" style="background: var(--uiu-orange-light); color: var(--uiu-orange);"><?php echo $active_initials; ?></div>
                <div style="flex: 1;">
                    <a href="profile.php?id=<?php echo $active_chat_user; ?>" style="font-size: 16px; font-weight: 700; color: var(--text-main); text-decoration: none;"><?php echo htmlspecialchars($active_user_name); ?></a>
                    <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($active_user_status); ?></div>
                </div>
                <button style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i class="fa-solid fa-ellipsis-vertical" style="font-size: 20px;"></i></button>
            </div>
            
            <div class="chat-box" id="chatBox"></div>
            
            <div class="chat-input-area">
                <button style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i class="fa-solid fa-image" style="font-size: 20px;"></i></button>
                <input type="text" id="msgInput" class="chat-input" placeholder="Type a message..." onkeypress="handleSend(event)">
                <button class="btn-primary" style="padding: 10px; border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px;" onclick="sendMessage()">
                    <i class="fa-solid fa-paper-plane" style="font-size: 16px;"></i>
                </button>
            </div>
            
            <script>
                const targetUserId = <?php echo $active_chat_user; ?>;
                const chatBox = document.getElementById('chatBox');
                let isUserScrolling = false;

                chatBox.addEventListener('scroll', function() {
                    if (chatBox.scrollTop + chatBox.clientHeight < chatBox.scrollHeight - 20) { isUserScrolling = true; } 
                    else { isUserScrolling = false; }
                });

                function loadMessages() {
                    let fd = new FormData(); fd.append('ajax_action', 'load_messages'); fd.append('target_id', targetUserId);
                    fetch('messages.php?user=' + targetUserId, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        chatBox.innerHTML = data.html;
                        if (!isUserScrolling) { chatBox.scrollTop = chatBox.scrollHeight; }
                    });
                }

                function sendMessage() {
                    let input = document.getElementById('msgInput');
                    let text = input.value.trim();
                    if(text === '') return;
                    let fd = new FormData(); fd.append('ajax_action', 'send_message'); fd.append('receiver_id', targetUserId); fd.append('message_text', text);
                    input.value = '';
                    fetch('messages.php?user=' + targetUserId, { method: 'POST', body: fd })
                    .then(r => r.json()).then(data => { if(data.status === 'success') { isUserScrolling = false; loadMessages(); } });
                }

                function handleSend(e) { if(e.key === 'Enter') sendMessage(); }
                loadMessages(); setInterval(loadMessages, 3000);
            </script>
            
        <?php else: ?>
            <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#f8fafc; color:var(--text-muted);">
                <i class="fa-regular fa-comments" style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;"></i>
                <h3 style="font-size:20px; font-weight:600; color:var(--text-main); margin-bottom:10px;">Your Messages</h3>
                <p style="font-size:14px;">Select a conversation from the left to start chatting.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function handleRequest(connId, type) {
    let action = (type === 'accept') ? 'accept_request' : 'decline_request';
    let fd = new FormData();
    fd.append('ajax_action', action);
    fd.append('conn_id', connId);
    
    fetch('messages.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            let card = document.getElementById('req-card-' + connId);
            card.style.opacity = '0.5';
            setTimeout(() => { card.remove(); location.reload(); }, 300); 
        }
    });
}
</script>

<script src="assets/main.js"></script>
</body>
</html>