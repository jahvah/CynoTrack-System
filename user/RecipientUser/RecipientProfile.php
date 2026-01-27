<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");
include("../../includes/alert.php");

// Ensure recipient is logged in
if (!isset($_SESSION['account_id'])) {
    die("Invalid session. Please login.");
}

$account_id = $_SESSION['account_id'];

// Fetch recipient data using account_id
$stmt = $conn->prepare("SELECT * FROM recipients_users WHERE account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$recipient = $stmt->get_result()->fetch_assoc();
?>

<h2>Complete Your Recipient Profile</h2>

<form method="POST" action="RecipientStore.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="update_profile">

    <!-- Profile Image -->
    <label>Profile Image</label>
    <input type="file" name="profile_image" accept="image/*">
    <?php if (!empty($recipient['profile_image'])): ?>
        <img src="../../uploads/<?= htmlspecialchars($recipient['profile_image']); ?>" width="120">
    <?php endif; ?>

    <!-- First Name -->
    <label>First Name</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($recipient['first_name'] ?? '') ?>" required>

    <!-- Last Name -->
    <label>Last Name</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($recipient['last_name'] ?? '') ?>" required>

    <!-- Preferences -->
    <label>Preferences</label>
    <textarea name="preferences"><?= htmlspecialchars($recipient['preferences'] ?? '') ?></textarea>

    <button type="submit">Save Profile</button>
</form>

<?php include("../../includes/footer.php"); ?>
