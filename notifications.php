<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = 'notifications.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    if ($_POST['ajax_action'] == 'mark_read') {
        $notif_id = intval($_POST['notif_id']);
        if ($notif_id == 0) {
            $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
        } else {
            $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notif_id AND user_id = $user_id");
        }
        echo json_encode(['status' => 'success']);
        exit();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<style>
    .notif-container {
        flex: 1;
        padding: 24px;
        max-width: 800px;
        margin: 0 auto;
    }

    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        background: #fff;
        padding: 20px 24px;
        border-radius: 12px;
        border: 1px solid var(--border-light);
    }

    .notif-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        background: #fff;
        border: 1px solid var(--border-light);
        border-bottom: none;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .notif-card:first-of-type {
        border-radius: 12px 12px 0 0;
    }

    .notif-card:last-of-type {
        border-bottom: 1px solid var(--border-light);
        border-radius: 0 0 12px 12px;
    }

    .notif-card:hover {
        background: #f8fafc;
    }

    /* Unread highlighting */
    .notif-card.unread {
        background: #F0F9FF;
    }

    .notif-card.unread:hover {
        background: #E0F2FE;
    }

    .unread-dot {
        width: 10px;
        height: 10px;
        background: #3B82F6;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .notif-icon-wrapper {
        position: relative;
    }

    .notif-badge {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        color: white;
    }

    .badge-like {
        background: #EF4444;
    }

    .badge-comment {
        background: #10B981;
    }

    .badge-connection {
        background: #3B82F6;
    }

    .badge-announcement {
        background: #F59E0B;
    }
</style>

<main class="notif-container">
    <div class="notif-header">
        <h2 style="font-size: 22px; color: var(--text-main);">Notifications</h2>
        <button class="btn-outline" style="font-size: 13px; padding: 6px 16px;" onclick="markAllRead()">Mark all as read</button>
    </div>

    <div class="notif-list">
        <?php
        $sql = "SELECT n.*, u.full_name FROM notifications n JOIN users u ON n.sender_id = u.id WHERE n.user_id = $user_id ORDER BY n.created_at DESC LIMIT 50";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $is_unread = ($row['is_read'] == 0) ? 'unread' : '';
                $icon_html = '';
                $message = '';
                $link = '#';

                switch ($row['type']) {
                    case 'like':
                        $icon_html = '<div class="notif-badge badge-like"><svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path></svg></div>';
                        $message = "<strong>" . htmlspecialchars($row['full_name']) . "</strong> liked your post.";
                        $link = "dashboard.php#post-card-" . $row['reference_id'];
                        break;
                    case 'comment':
                        $icon_html = '<div class="notif-badge badge-comment"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></div>';
                        $message = "<strong>" . htmlspecialchars($row['full_name']) . "</strong> commented on a post.";
                        $link = "dashboard.php#post-card-" . $row['reference_id'];
                        break;
                    case 'connection':
                        $icon_html = '<div class="notif-badge badge-connection"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg></div>';
                        $message = "<strong>" . htmlspecialchars($row['full_name']) . "</strong> sent you a connection request.";
                        $link = "profile.php?id=" . $row['sender_id'];
                        break;
                    default:
                        $icon_html = '<div class="notif-badge badge-announcement"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg></div>';
                        $message = "New Campus Announcement by <strong>" . htmlspecialchars($row['full_name']) . "</strong>.";
                        $link = "dashboard.php";
                        break;
                }
        ?>
                <a href="<?php echo $link; ?>" class="notif-card <?php echo $is_unread; ?>" onclick="markRead(<?php echo $row['id']; ?>, this)">
                    <div class="notif-icon-wrapper">
                        <div class="avatar-sm" style="background: var(--bg-light); border: 1px solid var(--border-light); color: var(--text-main);">
                            <?php echo strtoupper($row['full_name'][0]); ?>
                        </div>
                        <?php echo $icon_html; ?>
                    </div>
                    <div style="flex: 1; line-height: 1.4;">
                        <div style="font-size: 14px; color: var(--text-main);"><?php echo $message; ?></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;"><?php echo date('M d, g:i A', strtotime($row['created_at'])); ?></div>
                    </div>
                    <?php if ($row['is_read'] == 0): ?>
                        <div class="unread-dot"></div>
                    <?php endif; ?>
                </a>
        <?php
            }
        } else {
            echo '<div style="text-align:center; padding: 60px; background: #fff; border-radius: 12px; border: 1px solid var(--border-light); color: var(--text-muted);">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin-bottom:15px; opacity:0.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <p>No notifications yet. You are all caught up!</p>
                  </div>';
        }
        ?>
    </div>
</main>
</div>

<script>
    function markRead(notifId, element) {
        let fd = new FormData();
        fd.append('ajax_action', 'mark_read');
        fd.append('notif_id', notifId);

        fetch('notifications.php', {
            method: 'POST',
            body: fd
        });

        element.classList.remove('unread');
        let dot = element.querySelector('.unread-dot');
        if (dot) dot.remove();

        let badge = document.getElementById('notifBadge');
        if (badge) {
            let count = parseInt(badge.innerText) - 1;
            if (count <= 0) badge.style.display = 'none';
            else badge.innerText = count;
        }
    }

    function markAllRead() {
        let fd = new FormData();
        fd.append('ajax_action', 'mark_read');
        fd.append('notif_id', 0); 

        fetch('notifications.php', {
            method: 'POST',
            body: fd
        }).then(() => {
            document.querySelectorAll('.notif-card').forEach(el => el.classList.remove('unread'));
            document.querySelectorAll('.unread-dot').forEach(el => el.remove());
            let badge = document.getElementById('notifBadge');
            if (badge) badge.style.display = 'none';
        });
    }
</script>

<script src="assets/main.js"></script>
</body>

</html>