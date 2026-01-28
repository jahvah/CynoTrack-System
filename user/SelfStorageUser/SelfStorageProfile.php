<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");
include("../../includes/alert.php");

// Ensure user logged in
if (!isset($_SESSION['account_id'])) {
    die("Invalid session. Please login.");
}

$account_id = $_SESSION['account_id'];

// Fetch self storage user data
$stmt = $conn->prepare("SELECT * FROM self_storage_users WHERE account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<h2>Complete Your Self Storage Profile</h2>

<form method="POST" action="SelfStorageStore.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="update_profile">

    <label>Profile Image</label>
    <input type="file" name="profile_image" accept="image/*">
    <?php if (!empty($user['profile_image'])): ?>
        <img src="../../uploads/<?= htmlspecialchars($user['profile_image']); ?>" width="120">
    <?php endif; ?>

    <label>First Name</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>

    <label>Last Name</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>

    <label>Storage Details</label>
    <textarea name="storage_details" placeholder="Describe storage preferences or details..."><?= htmlspecialchars($user['storage_details'] ?? '') ?></textarea>

    <button type="submit">Save Profile</button>
</form>

<?php include("../../includes/footer.php"); ?>
