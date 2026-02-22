<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// admin access only

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminRecipientIndex.php");
    exit();
}

$recipient_id = intval($_GET['id']);

// fetch staff data with account info
$stmt = $conn->prepare("
    SELECT r.recipient_id, r.account_id, r.first_name, r.last_name, r.profile_image, r.preferences, a.username, a.email, a.status
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

<!--style toh para notif ng success or hindi-->
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
    <h2>Update Recipient</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['no_update'])): ?>
    <div class="message error"><?= $_SESSION['no_update']; ?></div>
    <?php unset($_SESSION['no_update']); ?>
    <?php endif; ?>


    <form action="AdminRecipientStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminRecipientUpdate">
        <input type="hidden" name="recipient_id" value="<?= $recipient['recipient_id']; ?>">
        <input type="hidden" name="account_id" value="<?= $recipient['account_id']; ?>">

        <!--ipapakita lang ang username at email, hindi pwedeng baguhin-->
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($recipient['username']); ?>" class="locked" disabled>
        <!--ipapakita lang ang username at email, hindi pwedeng baguhin-->
        <label>Email</label>
        <input type="text" value="<?= htmlspecialchars($recipient['email']); ?>" class="locked" disabled>
        
        <!-- Current Profile Picture -->
        <label>Current Profile Image</label>
        <?php if (!empty($recipient['profile_image'])): ?>
            <img src="../../../uploads/<?= htmlspecialchars($recipient['profile_image']); ?>" alt="Profile Image">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <!-- Editable fields -->
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

        <label>New Preferences</label>
        <input type="text" name="preferences" placeholder="Enter new preferences">


        <button type="submit">Update Recipient</button>
        <a href="AdminRecipientIndex.php" class="back-btn">← Back to Index</a>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>
