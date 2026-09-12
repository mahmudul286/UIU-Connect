<?php
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = 'marketplace.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'delete_item') {
    header('Content-Type: application/json');
    $item_id = intval($_POST['item_id']);

    $check = $conn->query("SELECT id, image_path FROM marketplace WHERE id = $item_id AND seller_id = $user_id");
    if ($check->num_rows > 0) {
        $item = $check->fetch_assoc();
        if ($item['image_path'] && file_exists($item['image_path'])) {
            unlink($item['image_path']);
        } 
        $conn->query("DELETE FROM marketplace WHERE id = $item_id");
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_title'])) {
    $title = $conn->real_escape_string($_POST['item_title']);
    $price = floatval($_POST['item_price']);
    $condition = $conn->real_escape_string($_POST['item_condition']);
    $category = $conn->real_escape_string($_POST['item_category']);
    $description = $conn->real_escape_string($_POST['item_description']);
    $image_path = NULL;

    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        $target_dir = "uploads/marketplace/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);
        $file_name = time() . '_' . basename($_FILES["item_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp']) && move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    $stmt = $conn->prepare("INSERT INTO marketplace (seller_id, item_title, price, item_condition, category, description, image_path, admin_approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')");
    $stmt->bind_param("isdssss", $user_id, $title, $price, $condition, $category, $description, $image_path);
    $stmt->execute();
    header("Location: marketplace.php");
    exit();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/dashboard.css">
<link rel="stylesheet" href="assets/marketplace.css">

<main class="marketplace-container">
    <div class="mp-header">
        <div>
            <h2 style="font-size: 22px; color: var(--text-main); margin-bottom: 4px;">Campus Marketplace</h2>
            <p style="font-size: 14px; color: var(--text-muted);">Buy, sell, or exchange textbooks and essentials
                securely.</p>
        </div>
        <button class="btn-primary" onclick="document.getElementById('addItemModal').style.display='flex'">
            <i class="fa-solid fa-plus" style="font-size: 18px; margin-right: 8px;"></i> Sell Item
        </button>
    </div>

    <div class="filters" id="mpFilters">
        <button class="filter-btn active" onclick="filterMarketplace('All', this)">All Items</button>
        <button class="filter-btn" onclick="filterMarketplace('Books', this)">Books & Notes</button>
        <button class="filter-btn" onclick="filterMarketplace('Electronics', this)">Electronics</button>
        <button class="filter-btn" onclick="filterMarketplace('Stationery', this)">Stationery</button>
        <button class="filter-btn" onclick="filterMarketplace('Others', this)">Others</button>
    </div>

    <div class="grid-container" id="marketplaceGrid">
        <?php
        $sql = "SELECT m.*, u.full_name FROM marketplace m JOIN users u ON m.seller_id = u.id WHERE m.admin_approval_status = 'approved' ORDER BY m.created_at DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cat = $row['category'] ? $row['category'] : 'Others';
                ?>
                <div class="product-card mp-item" data-category="<?php echo htmlspecialchars($cat); ?>"
                    id="item-<?php echo $row['id']; ?>">
                    <div class="product-img">
                        <span class="category-badge"><?php echo htmlspecialchars($cat); ?></span>

                        <?php if ($row['seller_id'] == $user_id): ?>
                            <button class="delete-btn" onclick="deleteItem(<?php echo $row['id']; ?>)" title="Delete Item">
                               <i class="fa-regular fa-trash-can" style="font-size: 12px;"></i>
                            </button>
                        <?php endif; ?>

                        <?php if ($row['image_path']): ?>
                            <img src="<?php echo $row['image_path']; ?>" alt="Item Image">
                        <?php else: ?>
                            <i class="fa-solid fa-image" style="font-size: 40px;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <div class="product-price">৳<?php echo number_format($row['price']); ?></div>
                        <div class="product-title"><?php echo htmlspecialchars($row['item_title']); ?></div>
                        <?php if ($row['description']): ?>
                            <div class="product-desc"><?php echo htmlspecialchars($row['description']); ?></div>
                        <?php endif; ?>

                        <div class="product-meta">
                            <span
                                style="background: var(--bg-light); padding: 4px 8px; border-radius: 6px; font-weight: 500; color: var(--text-main); border: 1px solid var(--border-light);"><?php echo htmlspecialchars($row['item_condition']); ?></span>
                            <span><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                        </div>
                        <div class="seller-info">
                            <a href="profile.php?id=<?php echo $row['seller_id']; ?>"
                                style="display: flex; align-items: center; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-light); text-decoration: none;">
                                <div class="seller-avatar"
                                    style="width: 24px; height: 24px; background: var(--bg-light); border: 1px solid var(--border-light); color: var(--text-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    <?php echo strtoupper($row['full_name'][0]); ?></div>
                                <span
                                    style="font-size: 12px; color: var(--text-muted); font-weight: 500; transition: color 0.2s;"
                                    onmouseover="this.style.color='var(--uiu-orange)'"
                                    onmouseout="this.style.color='var(--text-muted)'">Sold by
                                    <?php echo htmlspecialchars($row['full_name']); ?></span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--text-muted); background: #fff; border-radius: 12px; border: 1px solid var(--border-light);">No items available right now. Be the first to sell!</div>';
        }
        ?>
    </div>
</main>

<div class="modal-overlay" id="addItemModal">
    <div class="modal-content" style="max-width: 550px;">
        <div
            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-light); padding-bottom:12px;">
            <h3 style="font-size:18px; color: var(--text-main);">List New Item</h3>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);"
                onclick="document.getElementById('addItemModal').style.display='none'">
                <i class="fa-solid fa-xmark" style="font-size: 26px;"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div style="border: 2px dashed var(--border-light); border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; margin-bottom: 15px; background: var(--bg-light);"
                onclick="document.getElementById('itemImage').click()" id="uploadBox">
                            <i class="fa-solid fa-image" style="font-size: 28px; color: var(--uiu-orange); margin-bottom: 8px;"></i>
                <p style="font-size: 13px; color: var(--text-muted); font-weight: 500;" id="uploadText">Click to add
                    product image</p>
                <input type="file" name="item_image" id="itemImage" accept="image/*" style="display:none;"
                    onchange="showFileName(this)">
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 2;">
                    <label
                        style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Item
                        Title *</label>
                    <input type="text" name="item_title" class="poll-input" style="margin-bottom: 0;"
                        placeholder="e.g., Arduino Uno R3" required>
                </div>
                <div style="flex: 1;">
                    <label
                        style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Price
                        (৳) *</label>
                    <input type="number" name="item_price" class="poll-input" style="margin-bottom: 0;"
                        placeholder="0.00" required>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label
                        style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Category
                        *</label>
                    <select name="item_category" class="poll-input" style="margin-bottom: 0;" required>
                        <option value="Books">Books & Notes</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Stationery">Stationery</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label
                        style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Condition
                        *</label>
                    <select name="item_condition" class="poll-input" style="margin-bottom: 0;" required>
                        <option value="New">New</option>
                        <option value="Like New">Like New</option>
                        <option value="Good">Good</option>
                        <option value="Used">Used</option>
                    </select>
                </div>
            </div>

            <label
                style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">Description
                (Optional)</label>
            <textarea name="item_description" class="poll-input" rows="3"
                placeholder="Add some details about the item..."></textarea>

            <button type="submit" class="btn-primary"
                style="width:100%; margin-top: 10px; padding: 12px; font-size: 15px;">List Item for Sale</button>
        </form>
    </div>
</div>

<script>
    function showFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('uploadText').innerText = "Selected: " + input.files[0].name;
            document.getElementById('uploadBox').style.borderColor = "var(--uiu-orange)";
            document.getElementById('uploadBox').style.background = "var(--uiu-orange-light)";
        }
    }

    function filterMarketplace(category, btnElement) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');

        let items = document.querySelectorAll('.mp-item');
        items.forEach(item => {
            if (category === 'All' || item.getAttribute('data-category') === category) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function deleteItem(itemId) {
        if (confirm("Are you sure you want to delete this listing?")) {
            let fd = new FormData();
            fd.append('ajax_action', 'delete_item');
            fd.append('item_id', itemId);

            fetch('marketplace.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        let card = document.getElementById('item-' + itemId);
                        card.style.transition = "0.3s";
                        card.style.opacity = "0";
                        card.style.transform = "scale(0.9)";
                        setTimeout(() => card.remove(), 300);
                    } else {
                        alert("Error deleting item.");
                    }
                });
        }
    }
</script>
</body>

</html>