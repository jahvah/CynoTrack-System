<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// Admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$storage_user_id = intval($_GET['id']);

/* Fetch self-storage user + account info */
$stmt = $conn->prepare("
    SELECT s.storage_user_id, s.account_id, s.first_name, s.last_name, s.profile_image, a.username, a.status
    FROM self_storage_users s
    JOIN accounts a ON s.account_id = a.account_id
    WHERE s.storage_user_id = ?
");
$stmt->bind_param("i", $storage_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$user = $result->fetch_assoc();
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
label, select { display: block; margin-top: 15px; }
input, select { width: 100%; padding: 10px; margin: 10px 0; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
}
.locked { background: #eee; }
img {
    width: 120px;
    border-radius: 8px;
    display: block;
}
</style>

<div class="container">
    <h2>Update Self-Storage User</h2>

    <form action="AdminSelfStorageStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminSelfStorageUpdate">
        <input type="hidden" name="storage_user_id" value="<?= $user['storage_user_id']; ?>">
        <input type="hidden" name="account_id" value="<?= $user['account_id']; ?>">

        <!-- Username (locked) -->
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($user['username']); ?>" class="locked" disabled>

        <!-- Current Profile Picture -->
        <label>Current Profile Image</label>
        <?php if (!empty($user['profile_image'])): ?>
            <img src="../../../uploads/<?= htmlspecialchars($user['profile_image']); ?>" alt="Profile Image">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <!-- Fields to update -->
        <label>New Email</label>
        <input type="email" name="email" placeholder="Enter new email">

        <label>New First Name</label>
        <input type="text" name="first_name" placeholder="Enter new first name">

        <label>New Last Name</label>
        <input type="text" name="last_name" placeholder="Enter new last name">

        <label>Change Profile Image</label>
        <input type="file" name="profile_image">

        <!-- Account Status -->
        <label>Account Status</label>
        <select name="status">
            <option value="active" <?= $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
        </select>

        <button type="submit">Update User</button>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>
