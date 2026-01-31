<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminStaffIndex.php");
    exit();
}

$staff_id = intval($_GET['id']);

/* Get needed info */
$stmt = $conn->prepare("
    SELECT s.staff_id, s.account_id, s.profile_image, a.username
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

<style>

    label {
    display: block;
    margin-top: 15px;
}

img {
    display: block;
}

.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
input { width: 100%; padding: 10px; margin: 10px 0; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
}
.locked { background: #eee; }
img {
    width: 120px;
    margin: 10px 0;
    border-radius: 8px;
}
</style>

<div class="container">
    <h2>Update Staff</h2>

    <form action="AdminStaffStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminStaffUpdate">
        <input type="hidden" name="staff_id" value="<?= $staff['staff_id']; ?>">
        <input type="hidden" name="account_id" value="<?= $staff['account_id']; ?>">

        <!-- Username (locked) -->
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($staff['username']); ?>" class="locked" disabled>

        <!-- Current Profile Picture -->
        <label>Current Profile Image</label><br>
        <?php if (!empty($staff['profile_image'])): ?>
            <img src="../../uploads/<?= htmlspecialchars($staff['profile_image']); ?>" alt="Profile Image">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <!-- Empty Update Fields -->
        <label>New Email</label>
        <input type="email" name="email" placeholder="Enter new email">

        <label>New First Name</label>
        <input type="text" name="first_name" placeholder="Enter new first name">

        <label>New Last Name</label>
        <input type="text" name="last_name" placeholder="Enter new last name">

        <label>Change Profile Image</label>
        <input type="file" name="profile_image">

        <button type="submit">Update Staff</button>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>
