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
    header("Location: AdminStaffIndex.php");
    exit();
}

$staff_id = intval($_GET['id']);

// fetch staff data with account info
$stmt = $conn->prepare("
    SELECT s.staff_id,
    s.account_id,
    s.first_name, s.last_name,
    s.profile_image,
    a.username,
    a.email,
    a.status
    FROM staff s
    JOIN accounts a ON s.account_id = a.account_id
    WHERE s.staff_id = ?
");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminStaffIndex.php");
    exit();
}

$staff = $result->fetch_assoc();
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
    <h2>Update Staff</h2>

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

    <form action="AdminStaffStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminStaffUpdate">
        <input type="hidden" name="staff_id" value="<?= $staff['staff_id']; ?>">
        <input type="hidden" name="account_id" value="<?= $staff['account_id']; ?>">

        <!--ipapakita lang ang username at email, hindi pwedeng baguhin-->
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($staff['username']); ?>" class="locked" disabled>
        <!--ipapakita lang ang username at email, hindi pwedeng baguhin-->
        <label>Email</label>
        <input type="text" value="<?= htmlspecialchars($staff['email']); ?>" class="locked" disabled>

        <label>Current Profile Image</label>
        <?php if (!empty($staff['profile_image'])): ?>
            <img src="../../../uploads/<?= htmlspecialchars($staff['profile_image']); ?>" alt="Profile Image">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <label>New First Name</label>
        <input type="text" name="first_name" placeholder="Enter new first name">

        <label>New Last Name</label>
        <input type="text" name="last_name" placeholder="Enter new last name">

        <label>Change Profile Image</label>
        <input type="file" name="profile_image">

        <label>Account Status</label>
        <select name="status">
            <option value="active" <?= $staff['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= $staff['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="pending" <?= $staff['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
        </select>

        <button type="submit">Update Staff</button>
        <a href="AdminStaffIndex.php" class="back-btn">← Back to Index</a>

    </form>
</div>

<?php include('../../../includes/footer.php'); ?>
