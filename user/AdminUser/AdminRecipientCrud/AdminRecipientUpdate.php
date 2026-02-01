<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminRecipientIndex.php");
    exit();
}

$recipient_id = intval($_GET['id']);

/* Fetch recipient + account info */
$stmt = $conn->prepare("
    SELECT r.recipient_id, r.account_id, r.first_name, r.last_name, r.profile_image, a.username, a.status
    FROM recipients_users r
    JOIN accounts a ON r.account_id = a.account_id
    WHERE r.recipient_id = ?
");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminRecipientIndex.php");
    exit();
}

$recipient = $result->fetch_assoc();
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
    <h2>Update Recipient</h2>

    <form action="AdminRecipientStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminRecipientUpdate">
        <input type="hidden" name="recipient_id" value="<?= $recipient['recipient_id']; ?>">
        <input type="hidden" name="account_id" value="<?= $recipient['account_id']; ?>">

        <!-- Username (locked) -->
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($recipient['username']); ?>" class="locked" disabled>

        <!-- Current Profile Picture -->
        <label>Current Profile Image</label>
        <?php if (!empty($recipient['profile_image'])): ?>
            <img src="../../../uploads/<?= htmlspecialchars($recipient['profile_image']); ?>" alt="Profile Image">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <!-- Editable fields -->
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
            <option value="active" <?= $recipient['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= $recipient['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="pending" <?= $recipient['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
        </select>

        <button type="submit">Update Recipient</button>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>
