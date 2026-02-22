<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$storage_user_id = intval($_GET['id']);

// fetch self storage user data with account info
$stmt = $conn->prepare("
    SELECT s.storage_user_id, 
           s.account_id, 
           s.first_name, 
           s.last_name, 
           s.profile_image, 
           s.storage_details,
           a.username, 
           a.email, 
           a.status
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

$storage = $result->fetch_assoc();
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
label, select { display: block; margin-top: 15px; }
input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
    cursor: pointer;
}
.locked { background: #eee; }
img { width: 120px; border-radius: 8px; display: block; }
.message {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 5px;
}
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }

.back-btn {
    display: inline-block;
    margin-bottom: 15px;
    padding: 8px 12px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.back-btn:hover {
    background: #333;
}

</style>

<div class="container">
    <h2>Update Self Storage User</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="AdminSelfStorageStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminSelfStorageUpdate">
        <input type="hidden" name="storage_user_id" value="<?= $storage['storage_user_id']; ?>">
        <input type="hidden" name="account_id" value="<?= $storage['account_id']; ?>">

        <!-- Read only account info -->
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($storage['username']); ?>" class="locked" disabled>

        <label>Email</label>
        <input type="text" value="<?= htmlspecialchars($storage['email']); ?>" class="locked" disabled>

        <label>Current Profile Image</label>
        <?php if (!empty($storage['profile_image'])): ?>
            <img src="../../../uploads/<?= htmlspecialchars($storage['profile_image']); ?>" alt="Profile Image">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <label>New First Name</label>
        <input type="text" name="first_name" placeholder="Enter new first name">

        <label>New Last Name</label>
        <input type="text" name="last_name" placeholder="Enter new last name">

        <label>Storage Details</label>
        <textarea name="storage_details" rows="4" placeholder="Enter storage details"></textarea>

        <label>Change Profile Image</label>
        <input type="file" name="profile_image">

        <label>Account Status</label>
        <select name="status">
            <option value="active" <?= $storage['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= $storage['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="pending" <?= $storage['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
        </select>

        <button type="submit">Update Self Storage User</button>
        <a href="AdminSelfStorageIndex.php" class="back-btn">← Back to Index</a>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>
