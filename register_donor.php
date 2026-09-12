<?php
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $blood_group = $_POST['blood_group'];
    $last_donation = $_POST['last_donation'] ?: NULL;
    $status = $_POST['status'];

    $check = $conn->query("SELECT id FROM blood_bank WHERE user_id = $user_id");
    
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE blood_bank SET blood_group=?, last_donation=?, status=? WHERE user_id=?");
        $stmt->bind_param("sssi", $blood_group, $last_donation, $status, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO blood_bank (user_id, blood_group, last_donation, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $blood_group, $last_donation, $status);
    }
    
    if ($stmt->execute()) {
        header("Location: blood_bank.php");
        exit();
    }
}
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content" style="flex: 1; padding: 20px;">
    <div class="card" style="max-width: 500px; margin: 0 auto; margin-top: 30px;">
        <h2 style="color: var(--uiu-orange); margin-bottom: 20px; text-align: center;">Register as Blood Donor</h2>
        
        <form method="POST">
            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Blood Group *</label>
            <select name="blood_group" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid var(--border-light); border-radius: var(--radius-sm);">
                <option value="">Select Group</option>
                <option value="A+">A+</option><option value="A-">A-</option>
                <option value="B+">B+</option><option value="B-">B-</option>
                <option value="O+">O+</option><option value="O-">O-</option>
                <option value="AB+">AB+</option><option value="AB-">AB-</option>
            </select>

            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Last Donation Date (Optional)</label>
            <input type="date" name="last_donation" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid var(--border-light); border-radius: var(--radius-sm);">

            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Current Status *</label>
            <select name="status" required style="width: 100%; padding: 10px; margin-bottom: 25px; border: 1px solid var(--border-light); border-radius: var(--radius-sm);">
                <option value="Eligible">Eligible to Donate</option>
                <option value="Unavailable">Currently Unavailable</option>
            </select>

            <button type="submit" class="btn-primary" style="width: 100%; font-size: 16px;">Save Info</button>
        </form>
    </div>
</main>
</div>
</body>
</html>